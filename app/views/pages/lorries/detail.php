<!-- Lorry Detail Content Container -->
<div class="<?= !empty($inDashboard) ? '' : 'container' ?>" style="<?= !empty($inDashboard) ? '' : 'padding: var(--space-8) 0;' ?>">
    
    <!-- Redesigned Detail Layout Grid -->
    <div class="responsive-grid-detail">
        
        <!-- Left Column: Visual Media Card & Description -->
        <div class="flex flex-col gap-6">
            <!-- Redesigned Visual Media Card -->
            <div class="card" style="position: relative; overflow: hidden; border-radius: var(--radius-lg); box-shadow: var(--shadow-md);">
                <!-- Floating Availability Status Badge -->
                <div style="position: absolute; top: var(--space-4); left: var(--space-4); z-index: 10;">
                    <span class="badge status-<?= e($lorry['availability_status'] ?? 'available') ?>" style="padding: 6px 14px; font-size: 0.775rem; text-shadow: none;">
                        <i class="fa-solid fa-circle" style="font-size: 8px; margin-right: 6px;"></i><?= e(ucfirst($lorry['availability_status'] ?? 'available')) ?>
                    </span>
                </div>

                <?php 
                $primaryPhoto = null;
                if (!empty($lorry['photos'])) {
                    foreach ($lorry['photos'] as $p) {
                        if (!empty($p['is_primary'])) {
                            $primaryPhoto = $p['photo_path'];
                            break;
                        }
                    }
                    if (!$primaryPhoto) {
                        $primaryPhoto = $lorry['photos'][0]['photo_path'];
                    }
                }
                $photoUrl = getLorryPhotoUrl($primaryPhoto);
                if ($photoUrl): 
                ?>
                    <img src="<?= $photoUrl ?>" alt="<?= e($lorry['name'] ?? 'Lorry') ?>" class="card-img" style="height: 480px; object-fit: cover; width: 100%; display: block; transition: transform 0.5s var(--ease);">
                <?php else: ?>
                    <div style="height: 480px; display: flex; align-items: center; justify-content: center; font-size: 6rem; background: var(--gray-100); color: var(--gray-400);">
                        <i class="fa-solid fa-truck-moving"></i>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Description card -->
            <div class="card p-6" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <h3 class="mb-4" style="font-size: 1.25rem; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-file-waveform" style="color: var(--primary);"></i> Owner's Description</h3>
                <p style="white-space: pre-line; color: var(--gray-600); line-height: 1.7; font-size: 0.975rem;">
                    <?= !empty($lorry['description']) ? e($lorry['description']) : "No description provided by the owner." ?>
                </p>
            </div>
        </div>

        <!-- Right Column: Specs & Actions -->
        <div class="flex flex-col gap-6">
            <div>
                <!-- Title heading -->
                <h1 style="font-size: 2.25rem; font-weight: 800; margin-bottom: var(--space-2); color: var(--gray-900);"><?= e($lorry['name'] ?? 'Lorry') ?></h1>
                
                <!-- Rating review summary -->
                <div class="flex items-center gap-3 mb-4">
                    <?= renderStars((float)($lorry['avg_rating'] ?? 0)) ?>
                    <span class="text-sm text-muted" style="font-weight: 500;">
                        <strong><?= number_format((float)($lorry['avg_rating'] ?? 0), 1) ?></strong> / 5.0 (<?= (int)($lorry['total_trips'] ?? 0) ?> trips completed)
                    </span>
                </div>
            </div>

            <!-- Specifications Card -->
            <div class="card p-6" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);">
                <h3 class="mb-4" style="font-size: 1.25rem; display: flex; align-items: center; gap: 8px;"><i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Lorry Specifications</h3>
                
                <div class="flex flex-col gap-4">
                    <!-- Spec Line: Lorry Type -->
                    <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--gray-100); padding-bottom: var(--space-2);">
                        <span style="color: var(--gray-500); font-weight: 600;"><i class="fa-solid fa-truck-moving" style="margin-right: 8px; color: var(--primary);"></i> Type</span>
                        <span style="color: var(--gray-800); font-weight: 700;"><?= e(LORRY_TYPES[$lorry['lorry_type']]['en'] ?? $lorry['lorry_type'] ?? '—') ?></span>
                    </div>
                    <!-- Spec Line: Plate Number -->
                    <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--gray-100); padding-bottom: var(--space-2);">
                        <span style="color: var(--gray-500); font-weight: 600;"><i class="fa-solid fa-id-card" style="margin-right: 8px; color: var(--primary);"></i> Registration Plate</span>
                        <span class="badge status-accepted" style="font-weight: 700; border-radius: var(--radius-sm); font-size: 0.85rem; letter-spacing: 0.5px;"><?= e($lorry['plate_number'] ?? '—') ?></span>
                    </div>
                    <!-- Spec Line: Load Capacity -->
                    <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--gray-100); padding-bottom: var(--space-2);">
                        <span style="color: var(--gray-500); font-weight: 600;"><i class="fa-solid fa-weight-hanging" style="margin-right: 8px; color: var(--primary);"></i> Payload Capacity</span>
                        <span style="color: var(--gray-800); font-weight: 700;"><?= e($lorry['capacity_tonnes'] ?? '—') ?> Tons</span>
                    </div>
                    <!-- Spec Line: Rental Rate -->
                    <div class="flex justify-between items-center" style="border-bottom: 1px solid var(--gray-100); padding-bottom: var(--space-2);">
                        <span style="color: var(--gray-500); font-weight: 600;"><i class="fa-solid fa-tags" style="margin-right: 8px; color: var(--primary);"></i> Rental Rate</span>
                        <span class="text-primary" style="font-weight: 800; font-size: 1.1rem;"><?= number_format((float)($lorry['price_per_km'] ?? 0)) ?> TZS / km</span>
                    </div>
                    <!-- Spec Line: Base Location -->
                    <div class="flex justify-between items-center" style="padding-bottom: 2px;">
                        <span style="color: var(--gray-500); font-weight: 600;"><i class="fa-solid fa-location-dot" style="margin-right: 8px; color: var(--primary);"></i> Current Base</span>
                        <span style="color: var(--gray-800); font-weight: 700;">📍 <?= e($lorry['current_location'] ?? '—') ?></span>
                    </div>
                </div>
            </div>

            <!-- Booking CTA Actions Panel -->
            <div class="card p-6" style="background: var(--primary-bg); border: 1px dashed var(--primary-light); border-radius: var(--radius-lg);">
                <h3 class="mb-2" style="font-size: 1.15rem; color: var(--primary-dark);"><i class="fa-solid fa-wallet"></i> Hire Lorry</h3>
                <p class="text-sm text-muted mb-4">Select this verified vehicle and request instant booking options directly with the owner.</p>
                
                <div class="flex flex-col gap-3">
                    <?php if (isLoggedIn() && currentUserRole() === 'customer'): ?>
                        <a href="<?= APP_URL ?>/bookings/create/<?= (int)$lorry['id'] ?>" class="btn btn-accent btn-lg btn-block"><i class="fa-solid fa-calendar-check"></i> Book Lorry Now</a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="<?= APP_URL ?>/auth/login?return=<?= urlencode(trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '', '/')) ?>" class="btn btn-primary btn-lg btn-block"><i class="fa-solid fa-right-to-bracket"></i> Login to Book</a>
                    <?php endif; ?>
                    <a href="<?= APP_URL ?>/lorries/search" class="btn btn-outline btn-block" style="background: white;"><i class="fa-solid fa-arrow-left"></i> Return to Search</a>
                </div>
            </div>
        </div>
    </div>

    <!-- ─── Redesigned Customer Reviews Section ────────────────── -->
    <div class="mt-12">
        <h2 class="mb-6" style="font-size: 1.5rem; display: flex; align-items: center; gap: 10px;"><i class="fa-solid fa-comments" style="color: var(--accent);"></i> Customer Feedback</h2>
        
        <?php if (empty($lorry['reviews'])): ?>
            <div class="card p-8 text-center" style="border-radius: var(--radius-lg);">
                <p style="font-size: 2.5rem; color: var(--gray-300);"><i class="fa-solid fa-comment-slash"></i></p>
                <h4 class="mt-3" style="color: var(--gray-700);">No Reviews Yet</h4>
                <p class="text-muted text-sm mt-1">Be the first customer to book this lorry and leave feedback about your trip!</p>
            </div>
        <?php else: ?>
            <div class="flex flex-col gap-6">
                <?php foreach ($lorry['reviews'] as $review): 
                    // Generate author initials for premium user avatar
                    $words = explode(' ', trim($review['reviewer_name'] ?? 'Anonymous'));
                    $initials = strtoupper(substr($words[0], 0, 1));
                    if (count($words) > 1) {
                        $initials .= strtoupper(substr($words[count($words) - 1], 0, 1));
                    }
                ?>
                    <div class="card p-6" style="border-radius: var(--radius-lg); box-shadow: var(--shadow-sm); border: 1px solid rgba(226,232,240,0.6);">
                        <!-- Review Header Area -->
                        <div class="flex justify-between items-start mb-4" style="flex-wrap: wrap; gap: var(--space-3);">
                            <div class="flex items-center gap-3">
                                <!-- User Initials Avatar Circle -->
                                <div style="width: 48px; height: 48px; border-radius: var(--radius-full); background: var(--primary-bg); color: var(--primary); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; border: 2px solid var(--primary-light);">
                                    <?= e($initials) ?>
                                </div>
                                <div>
                                    <strong style="font-size: 1.05rem; color: var(--gray-900); display: block;"><?= e($review['reviewer_name'] ?? 'Verified Customer') ?></strong>
                                    <div class="mt-1">
                                        <?= renderStars((float)$review['rating']) ?>
                                    </div>
                                </div>
                            </div>
                            <!-- Date Stamp -->
                            <span class="text-sm text-muted" style="font-weight: 500;"><i class="fa-solid fa-clock" style="margin-right: 4px;"></i> <?= formatDate($review['created_at']) ?></span>
                        </div>
                        
                        <!-- Review Comment -->
                        <div style="padding-left: 60px;">
                            <p style="color: var(--gray-700); font-style: italic; font-size: 1rem; line-height: 1.6; position: relative;">
                                <span style="font-size: 2rem; color: var(--primary-light); opacity: 0.3; position: absolute; left: -25px; top: -10px; font-family: Georgia, serif;">“</span>
                                <?= e($review['comment']) ?>
                                <span style="font-size: 2rem; color: var(--primary-light); opacity: 0.3; line-height: 0; font-family: Georgia, serif;">”</span>
                            </p>
                            
                            <!-- Nested Owner Reply -->
                            <?php if (!empty($review['owner_reply'])): ?>
                                <div class="mt-4 p-4" style="background: var(--gray-50); border-left: 4px solid var(--primary); border-radius: var(--radius-sm);">
                                    <strong class="text-sm text-primary" style="display: flex; align-items: center; gap: 6px;"><i class="fa-solid fa-reply"></i> Owner Response:</strong>
                                    <p class="mb-0 mt-2 text-sm" style="color: var(--gray-600); line-height: 1.5; font-style: normal;">
                                        <?= e($review['owner_reply']) ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
