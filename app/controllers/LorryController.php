<?php
/**
 * app/controllers/LorryController.php
 * Handles lorry search, listing, add/edit, and API endpoints.
 */

declare(strict_types=1);

class LorryController
{
    /**
     * GET /lorries/search — Search/browse lorries page.
     */
    public function search(): void
    {
        $pageTitle = 'Search Lorries';
        $currentPage = 'search';

        $filters = [
            'type'      => get('type'),
            'location'  => get('location'),
            'max_price' => get('max_price'),
        ];
        $page = max(1, getInt('page', 1));
        $perPage = 12;
        $offset = ($page - 1) * $perPage;

        $lorryModel = new Lorry();
        $totalResults = $lorryModel->countSearch($filters);
        $totalPages = (int)ceil($totalResults / $perPage);
        $lorries = $lorryModel->search($filters, 'avg_rating', $perPage, $offset);

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/lorries/search.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /lorries/{id} — Lorry detail page.
     */
    public function detail(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $lorryModel = new Lorry();
        $lorry = $lorryModel->getDetailById($id);
        if ($lorry) {
            $lorry['reviews'] = $lorryModel->getReviews($id);
        }

        if (!$lorry || ($lorry['approval_status'] ?? '') !== 'approved') {
            flashMessage('error', 'Lorry not found.');
            redirect(APP_URL . '/lorries/search');
        }

        $pageTitle = $lorry['name'] ?? 'Lorry Details';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/lorries/detail.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /lorries/add — Show add lorry form (owners only).
     */
    public function addForm(): void
    {
        requireRole('lorry_owner');
        $pageTitle = 'Add Lorry';

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/lorries/add.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /lorries/add — Process add lorry.
     */
    public function add(): void
    {
        requireRole('lorry_owner');
        verifyCsrf();

        $lorryModel = new Lorry();

        $data = [
            'owner_id'         => currentUserId(),
            'name'             => sanitizeString(post('title')),
            'lorry_type'       => post('lorry_type'),
            'plate_number'     => strtoupper(sanitizeString(post('plate_number'))),
            'capacity_tonnes'  => (float)post('capacity_tons'),
            'price_per_km'     => (float)post('price_per_km'),
            'current_location' => sanitizeString(post('location')),
            'description'      => sanitizeString(post('description')),
        ];

        $lorryId = $lorryModel->addLorry($data);

        // Handle photo upload
        if ($lorryId && !empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = STORAGE_PATH . '/lorries/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $filename = 'lorry_' . $lorryId . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $uploadDir . $filename)) {
                $lorryModel->addPhoto($lorryId, $filename, true);
            }
        }

        if ($lorryId) {
            logSystemAction('lorry_added', "Owner listed lorry: {$data['name']} ({$data['plate_number']}), ID: {$lorryId}");
            if (isAjax()) {
                jsonResponse(['success' => true, 'message' => 'Lorry listed! It will appear after admin approval. / Lori limeorodheshwa!']);
            }
            flashMessage('success', 'Lorry listed! It will appear after admin approval. / Lori limeorodheshwa!');
            redirect(APP_URL . '/lorries/mine');
        } else {
            if (isAjax()) {
                jsonResponse(['success' => false, 'message' => 'Failed to add lorry. Please try again.']);
            }
            flashMessage('error', 'Failed to add lorry. Please try again.');
            redirect(APP_URL . '/lorries/add');
        }
    }

    /**
     * GET /lorries/mine — Owner's lorry list.
     */
    public function mine(): void
    {
        requireRole('lorry_owner');
        $pageTitle = 'My Lorries';

        $lorryModel = new Lorry();
        $lorries = $lorryModel->getByOwner(currentUserId());

        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/lorries/mine.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * GET /lorries/{id}/edit — Show edit form.
     */
    public function editForm(array $params): void
    {
        requireRole('lorry_owner');
        $id = (int)($params['id'] ?? 0);
        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($id);

        if (!$lorry || (int)$lorry['owner_id'] !== currentUserId()) {
            flashMessage('error', 'Lorry not found or access denied.');
            redirect(APP_URL . '/lorries/mine');
        }

        $pageTitle = 'Edit Lorry';
        require_once VIEW_PATH . '/layouts/header.php';
        require_once VIEW_PATH . '/pages/lorries/edit.php';
        require_once VIEW_PATH . '/layouts/footer.php';
    }

    /**
     * POST /lorries/{id}/edit — Process edit.
     */
    public function edit(array $params): void
    {
        requireRole('lorry_owner');
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($id);

        if (!$lorry || (int)$lorry['owner_id'] !== currentUserId()) {
            flashMessage('error', 'Access denied.');
            redirect(APP_URL . '/lorries/mine');
        }

        $lorryModel->update($id, [
            'name'             => sanitizeString(post('title')),
            'lorry_type'       => post('lorry_type'),
            'capacity_tonnes'  => (float)post('capacity_tons'),
            'price_per_km'     => (float)post('price_per_km'),
            'current_location' => sanitizeString(post('location')),
            'description'      => sanitizeString(post('description')),
        ]);

        logSystemAction('lorry_updated', "Owner updated lorry ID: {$id}, Name: " . sanitizeString(post('title')));
        if (isAjax()) {
            jsonResponse(['success' => true, 'message' => 'Lorry updated successfully!']);
        }
        flashMessage('success', 'Lorry updated successfully!');
        redirect(APP_URL . '/lorries/mine');
    }

    /**
     * POST /lorries/{id}/delete — Soft delete lorry.
     */
    public function delete(array $params): void
    {
        requireRole('lorry_owner');
        verifyCsrf();
        $id = (int)($params['id'] ?? 0);
        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($id);

        if (!$lorry || (int)$lorry['owner_id'] !== currentUserId()) {
            flashMessage('error', 'Access denied.');
            redirect(APP_URL . '/lorries/mine');
        }

        $lorryModel->softDelete($id);
        logSystemAction('lorry_deleted', "Owner deleted lorry ID: {$id}");
        if (isAjax()) {
            jsonResponse(['success' => true, 'message' => 'Lorry removed. / Lori limefutwa.']);
        }
        flashMessage('success', 'Lorry removed. / Lori limefutwa.');
        redirect(APP_URL . '/lorries/mine');
    }

    /**
     * GET /api/v1/lorries/search — JSON search endpoint.
     */
    public function apiSearch(): void
    {
        $filters = [
            'type'     => get('type'),
            'location' => get('location'),
        ];
        $lorryModel = new Lorry();
        $lorries = $lorryModel->search($filters, 'avg_rating', 20, 0);
        jsonResponse(['success' => true, 'data' => $lorries]);
    }

    /**
     * GET /api/v1/lorries/{id} — JSON single lorry.
     */
    public function apiDetail(array $params): void
    {
        $id = (int)($params['id'] ?? 0);
        $lorryModel = new Lorry();
        $lorry = $lorryModel->findById($id);
        if (!$lorry) {
            jsonResponse(['success' => false, 'error' => 'Not found'], 404);
        }
        jsonResponse(['success' => true, 'data' => $lorry]);
    }
}
