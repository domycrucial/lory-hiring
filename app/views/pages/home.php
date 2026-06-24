<!-- ─── Hero Section with Modern Gradients ──────────────────────── -->
<!-- Main banner area containing title, subtitle, and primary call-to-actions -->
<section class="hero">
    <div class="container hero-content">
        <!-- Giant bold page headline -->
        <h1><i class="fa-solid fa-truck-fast"></i> Hire a Lorry in Tanzania</h1>
        <!-- Descriptive page summary -->
        <p>Fast, secure, and affordable lorry transport. Connect with verified lorry owners across Dar es Salaam, Arusha, Mwanza, and beyond.</p>
        <!-- Horizontal actions buttons -->
        <div class="flex items-center justify-between gap-4" style="justify-content: center;">
            <!-- Primary search action -->
            <a href="<?= APP_URL ?>/lorries/search" class="btn btn-accent btn-lg"><i class="fa-solid fa-magnifying-glass"></i> Search Lorries</a>
            <!-- Registration toggle -->
            <a href="<?= APP_URL ?>/auth/register" class="btn btn-outline btn-lg" style="border-color:white; color:white;"><i class="fa-solid fa-user-plus"></i> Register Free</a>
        </div>
    </div>
</section>

<!-- ─── Stats Bar Component ─────────────────────────────────────────── -->
<!-- Stats cards positioned cleanly below the hero banner -->
<section class="container" style="margin-top: var(--space-8); position: relative; z-index: 10;">
    <div class="grid grid-3">
        <!-- Verified trucks count -->
        <div class="stat-card">
            <div class="stat-value"><i class="fa-solid fa-circle-check" style="font-size: 1.5rem; color: var(--primary);"></i> 100+</div>
            <div class="stat-label">Verified Lorries</div>
        </div>
        <!-- Finished deliveries count -->
        <div class="stat-card">
            <div class="stat-value"><i class="fa-solid fa-route" style="font-size: 1.5rem; color: var(--primary);"></i> 500+</div>
            <div class="stat-label">Completed Trips</div>
        </div>
        <!-- Active regions count -->
        <div class="stat-card">
            <div class="stat-value"><i class="fa-solid fa-map" style="font-size: 1.5rem; color: var(--primary);"></i> 26</div>
            <div class="stat-label">Regions Covered</div>
        </div>
    </div>
</section>

<!-- ─── How It Works Step-by-Step Flow ─────────────────────────────── -->
<!-- Instructional layout to guide customer journey -->
<section class="container" style="padding: var(--space-12) 0;">
    <!-- Centered section heading -->
    <h2 class="text-center mb-8"><i class="fa-solid fa-circle-info"></i> How It Works</h2>
    <div class="grid grid-3">
        <!-- Step 1: Browse listings -->
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 3rem; margin-bottom: var(--space-4); color: var(--primary);"><i class="fa-solid fa-magnifying-glass-location"></i></div>
                <h3>1. Search</h3>
                <p class="card-text">Browse verified lorries by type, location, capacity, and km price rate.</p>
            </div>
        </div>
        <!-- Step 2: Book transaction -->
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 3rem; margin-bottom: var(--space-4); color: var(--primary);"><i class="fa-solid fa-file-signature"></i></div>
                <h3>2. Book</h3>
                <p class="card-text">Submit your booking request with pickup and delivery addresses.</p>
            </div>
        </div>
        <!-- Step 3: Mobile Payment -->
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 3rem; margin-bottom: var(--space-4); color: var(--primary);"><i class="fa-solid fa-wallet"></i></div>
                <h3>3. Pay & Go</h3>
                <p class="card-text">Pay securely via M-Pesa, Airtel Money, Halopesa, or bank card.</p>
            </div>
        </div>
    </div>
</section>

<!-- ─── Featured Lorries Carousel Grid ────────────────────────────── -->
<!-- Renders only if approved lorry list is populated -->
<?php if (!empty($featuredLorries)): ?>
<section class="container mb-8">
    <!-- Centered section heading -->
    <h2 class="text-center mb-8"><i class="fa-solid fa-star" style="color: var(--accent);"></i> Featured Lorries</h2>
    
    <div class="flex flex-col gap-6" style="max-width: 900px; margin: 0 auto;">
        <!-- Iterate list items -->
        <?php foreach ($featuredLorries as $lorry): ?>
        <div class="card-horizontal">
            <!-- Smooth hover scale zoom image wrapper -->
            <div class="card-img-wrapper">
                <?php 
                $photoUrl = getLorryPhotoUrl($lorry['primary_photo'] ?? null);
                if ($photoUrl): 
                ?>
                    <img src="<?= $photoUrl ?>" alt="<?= e($lorry['name'] ?? 'Lorry') ?>" class="card-img">
                <?php else: ?>
                    <!-- Icon placeholder layout fallback -->
                    <div style="width:100%; height:100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; background: var(--gray-100);">🚛</div>
                <?php endif; ?>
            </div>
            <!-- Listing details body -->
            <div class="card-body">
                <div class="card-content-top">
                    <div>
                        <!-- Title heading -->
                        <h3 class="card-title" style="margin-bottom: var(--space-2);"><?= e($lorry['name'] ?? $lorry['title'] ?? 'Lorry') ?></h3>
                        <!-- Spec list lines -->
                        <p class="card-text" style="margin-bottom: 0;">
                            <span style="margin-right: var(--space-4); display: inline-flex; align-items: center; gap: 6px;"><i class="fa-solid fa-location-dot" style="color: var(--primary);"></i> <?= e($lorry['current_location'] ?? $lorry['location'] ?? 'Tanzania') ?></span>
                            <span style="display: inline-flex; align-items: center; gap: 6px;"><i class="fa-solid fa-weight-hanging" style="color: var(--primary);"></i> <?= e($lorry['capacity_tonnes'] ?? $lorry['capacity_tons'] ?? '—') ?> tons</span>
                        </p>
                    </div>
                    <!-- Star ratings row -->
                    <div class="text-right">
                        <?= renderStars((float)($lorry['avg_rating'] ?? 0)) ?>
                        <div class="text-sm text-muted" style="margin-top: 2px;">(<?= (int)($lorry['total_trips'] ?? 0) ?> trips)</div>
                    </div>
                </div>
                
                <div class="card-content-bottom">
                    <div style="font-size: 1.2rem; color: var(--gray-900);">
                        Rate: <strong class="text-primary"><?= number_format((float)($lorry['price_per_km'] ?? 0)) ?> TZS/km</strong>
                    </div>
                    <!-- Detail link button -->
                    <a href="<?= APP_URL ?>/lorries/<?= (int)$lorry['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> View Details</a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Browse all CTAs -->
    <div class="text-center mt-8">
        <a href="<?= APP_URL ?>/lorries/search" class="btn btn-outline btn-lg"><i class="fa-solid fa-arrow-right-to-bracket"></i> View All Lorries →</a>
    </div>
</section>
<?php endif; ?>

<!-- ─── Call to Action (CTA) Section ───────────────────────────── -->
<!-- Invites lorry owners to register and host on the platform -->
<section style="background: var(--grad-dark); color: white; padding: var(--space-12) 0; text-align: center; border-radius: var(--radius-xl) var(--radius-xl) 0 0;">
    <div class="container">
        <!-- CTA heading -->
        <h2 style="color: white; margin-bottom: var(--space-3);"><i class="fa-solid fa-handshake" style="color: var(--accent);"></i> Own a Lorry? Start Earning Today!</h2>
        <!-- Supporting copy -->
        <p style="color: rgba(255,255,255,.8); max-width: 550px; margin: 0 auto var(--space-6);">Register as a lorry owner, list your vehicles, and start receiving verified booking requests from customers across Tanzania.</p>
        <!-- Owner signup button -->
        <a href="<?= APP_URL ?>/auth/register" class="btn btn-accent btn-lg"><i class="fa-solid fa-user-plus"></i> Register as Owner</a>
    </div>
</section>
