<!-- ─── Hero Section with Modern Gradients ──────────────────────── -->
<!-- Main banner area containing title, subtitle, and primary call-to-actions -->
<section class="hero">
    <div class="container hero-content">
        <!-- Giant bold page headline -->
        <h1><i class="fa-solid fa-truck-fast"></i> <?= currentLang() === 'sw' ? 'Kodi Lori nchini Tanzania' : 'Hire a Lorry in Tanzania' ?></h1>
        <!-- Descriptive page summary -->
        <p><?= currentLang() === 'sw' ? 'Usafiri wa haraka, salama na wa bei nafuu. Ungana na wamiliki wa malori waliothibitishwa nchini kote.' : 'Fast, secure, and affordable lorry transport. Connect with verified lorry owners across Dar es Salaam, Arusha, Mwanza, and beyond.' ?></p>
        <!-- Horizontal actions buttons -->
        <div class="flex items-center justify-between gap-4" style="justify-content: center;">
            <!-- Primary search action -->
            <a href="<?= APP_URL ?>/lorries/search" class="btn btn-accent btn-lg"><i class="fa-solid fa-magnifying-glass"></i> <?= currentLang() === 'sw' ? 'Tafuta Malori' : 'Search Lorries' ?></a>
            <!-- Registration toggle -->
            <a href="<?= APP_URL ?>/auth/register" class="btn btn-outline btn-lg" style="border-color:white; color:white;"><i class="fa-solid fa-user-plus"></i> <?= currentLang() === 'sw' ? 'Jisajili Bure' : 'Register Free' ?></a>
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
            <div class="stat-label"><?= currentLang() === 'sw' ? 'Malori Yaliyothibitishwa' : 'Verified Lorries' ?></div>
        </div>
        <!-- Finished deliveries count -->
        <div class="stat-card">
            <div class="stat-value"><i class="fa-solid fa-route" style="font-size: 1.5rem; color: var(--primary);"></i> 500+</div>
            <div class="stat-label"><?= currentLang() === 'sw' ? 'Safari Zilizokamilika' : 'Completed Trips' ?></div>
        </div>
        <!-- Active regions count -->
        <div class="stat-card">
            <div class="stat-value"><i class="fa-solid fa-map" style="font-size: 1.5rem; color: var(--primary);"></i> 26</div>
            <div class="stat-label"><?= currentLang() === 'sw' ? 'Mikoa Inayofikiwa' : 'Regions Covered' ?></div>
        </div>
    </div>

    <!-- City Filter Quick Links -->
    <div class="text-center mt-6">
        <span class="text-muted text-xs font-bold mr-2 uppercase tracking-wider">Search by City / Tafuta kwa Mji:</span>
        <div style="display: inline-flex; flex-wrap: wrap; gap: 8px; justify-content: center; margin-top: 8px;">
            <a href="<?= APP_URL ?>/lorries/search?location=Dar+es+Salaam" class="btn btn-outline btn-xs" style="padding: 4px 12px; font-size: 0.75rem;">Dar es Salaam</a>
            <a href="<?= APP_URL ?>/lorries/search?location=Arusha" class="btn btn-outline btn-xs" style="padding: 4px 12px; font-size: 0.75rem;">Arusha</a>
            <a href="<?= APP_URL ?>/lorries/search?location=Mwanza" class="btn btn-outline btn-xs" style="padding: 4px 12px; font-size: 0.75rem;">Mwanza</a>
            <a href="<?= APP_URL ?>/lorries/search?location=Dodoma" class="btn btn-outline btn-xs" style="padding: 4px 12px; font-size: 0.75rem;">Dodoma</a>
            <a href="<?= APP_URL ?>/lorries/search?location=Mbeya" class="btn btn-outline btn-xs" style="padding: 4px 12px; font-size: 0.75rem;">Mbeya</a>
        </div>
    </div>
</section>

<!-- ─── How It Works Step-by-Step Flow ─────────────────────────────── -->
<!-- Instructional layout to guide customer journey -->
<section class="container" style="padding: var(--space-12) 0 var(--space-6) 0;">
    <!-- Centered section heading -->
    <h2 class="text-center mb-8"><i class="fa-solid fa-circle-info"></i> <?= currentLang() === 'sw' ? 'Jinsi Inavyofanya Kazi' : 'How It Works' ?></h2>
    <div class="grid grid-3">
        <!-- Step 1: Browse listings -->
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 3rem; margin-bottom: var(--space-4); color: var(--primary);"><i class="fa-solid fa-magnifying-glass-location"></i></div>
                <h3><?= currentLang() === 'sw' ? '1. Tafuta' : '1. Search' ?></h3>
                <p class="card-text"><?= currentLang() === 'sw' ? 'Vinjari malori yaliyothibitishwa kwa aina, mahali, uwezo na bei ya kila kilomita.' : 'Browse verified lorries by type, location, capacity, and km price rate.' ?></p>
            </div>
        </div>
        <!-- Step 2: Book transaction -->
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 3rem; margin-bottom: var(--space-4); color: var(--primary);"><i class="fa-solid fa-file-signature"></i></div>
                <h3><?= currentLang() === 'sw' ? '2. Weka Uhifadhi' : '2. Book' ?></h3>
                <p class="card-text"><?= currentLang() === 'sw' ? 'Tuma ombi lako la uhifadhi ukiweka anwani za kuchukulia na kufikisha mizigo.' : 'Submit your booking request with pickup and delivery addresses.' ?></p>
            </div>
        </div>
        <!-- Step 3: Mobile Payment -->
        <div class="card text-center">
            <div class="card-body">
                <div style="font-size: 3rem; margin-bottom: var(--space-4); color: var(--primary);"><i class="fa-solid fa-wallet"></i></div>
                <h3><?= currentLang() === 'sw' ? '3. Lipa & Safari' : '3. Pay & Go' ?></h3>
                <p class="card-text"><?= currentLang() === 'sw' ? 'Lipa kwa usalama kupitia M-Pesa, Airtel Money, Halopesa, au kadi ya benki.' : 'Pay securely via M-Pesa, Airtel Money, Halopesa, or bank card.' ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ─── Trust Signals Section ──────────────────────────────────────── -->
<section style="background: var(--gray-100); padding: var(--space-12) 0; margin-bottom: var(--space-12); border-radius: var(--radius-lg);">
    <div class="container text-center">
        <h2 class="mb-8"><i class="fa-solid fa-circle-check" style="color: var(--primary);"></i> <?= currentLang() === 'sw' ? 'Kwa Nini Uchague OLHS?' : 'Why Choose OLHS?' ?></h2>
        <div class="grid grid-3">
            <div class="p-4">
                <div style="font-size: 2.5rem; color: var(--success); margin-bottom: var(--space-4);"><i class="fa-solid fa-user-shield"></i></div>
                <h3><?= currentLang() === 'sw' ? 'Watoa Huduma Waliothibitishwa' : 'Verified Operators' ?></h3>
                <p class="text-muted text-sm mt-2"><?= currentLang() === 'sw' ? 'Kila mmiliki na gari linahakikiwa kikamilifu na wasimamizi wetu kabla ya kufanya safari.' : 'Every lorry owner and vehicle is fully vetted and approved by our system administrators before they can take trips.' ?></p>
            </div>
            <div class="p-4">
                <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: var(--space-4);"><i class="fa-solid fa-shield-halved"></i></div>
                <h3><?= currentLang() === 'sw' ? 'Miamala Salama' : 'Secure Transactions' ?></h3>
                <p class="text-muted text-sm mt-2"><?= currentLang() === 'sw' ? 'Malipo yanafanyika kupitia mifumo salama ya pesa za mitandao ya simu. Pesa zinalindwa mpaka safari ikamilike.' : 'Payments are processed instantly using protected mobile money APIs. Funds are secured until the transport is completed.' ?></p>
            </div>
            <div class="p-4">
                <div style="font-size: 2.5rem; color: var(--accent); margin-bottom: var(--space-4);"><i class="fa-solid fa-headset"></i></div>
                <h3><?= currentLang() === 'sw' ? 'Huduma kwa Wateja' : 'Dedicated Support' ?></h3>
                <p class="text-muted text-sm mt-2"><?= currentLang() === 'sw' ? 'Timu yetu ya usaidizi ipo tayari wakati wote kutatua changamoto na kuhakikisha huduma bora.' : 'Our customer support team is always available to help resolve issues, manage disputes, and ensure smooth operations.' ?></p>
            </div>
        </div>
    </div>
</section>

<!-- ─── Featured Lorries Carousel Grid ────────────────────────────── -->
<!-- Renders only if approved lorry list is populated -->
<?php if (!empty($featuredLorries)): ?>
<section class="container mb-8">
    <!-- Centered section heading -->
    <h2 class="text-center mb-8"><i class="fa-solid fa-star" style="color: var(--accent);"></i> <?= currentLang() === 'sw' ? 'Malori Yaliyopendekezwa' : 'Featured Lorries' ?></h2>
    
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
                    <div style="width:100%; height:100%; display: flex; align-items: center; justify-content: center; font-size: 4rem; background: var(--gray-100); color: var(--gray-400);"><i class="fa-solid fa-truck"></i></div>
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
                            <span style="display: inline-flex; align-items: center; gap: 6px;"><i class="fa-solid fa-weight-hanging" style="color: var(--primary);"></i> <?= e($lorry['capacity_tonnes'] ?? $lorry['capacity_tons'] ?? '—') ?> <?= currentLang() === 'sw' ? 'tani' : 'tons' ?></span>
                        </p>
                    </div>
                    <!-- Star ratings row -->
                    <div class="text-right">
                        <?= renderStars((float)($lorry['avg_rating'] ?? 0)) ?>
                        <div class="text-sm text-muted" style="margin-top: 2px;">(<?= (int)($lorry['total_trips'] ?? 0) ?> <?= currentLang() === 'sw' ? 'safari' : 'trips' ?>)</div>
                    </div>
                </div>
                
                <div class="card-content-bottom">
                    <div style="font-size: 1.2rem; color: var(--gray-900);">
                        <?= currentLang() === 'sw' ? 'Gharama' : 'Rate' ?>: <strong class="text-primary"><?= number_format((float)($lorry['price_per_km'] ?? 0)) ?> TZS/km</strong>
                    </div>
                    <!-- Detail link button -->
                    <a href="<?= APP_URL ?>/lorries/<?= (int)$lorry['id'] ?>" class="btn btn-primary btn-sm"><i class="fa-solid fa-eye"></i> <?= currentLang() === 'sw' ? 'Angalia Maelezo' : 'View Details' ?></a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Browse all CTAs -->
    <div class="text-center mt-8">
        <a href="<?= APP_URL ?>/lorries/search" class="btn btn-outline btn-lg"><i class="fa-solid fa-arrow-right-to-bracket"></i> <?= currentLang() === 'sw' ? 'Angalia Malori Yote' : 'View All Lorries' ?> →</a>
    </div>
</section>
<?php endif; ?>

<!-- ─── Call to Action (CTA) Section ───────────────────────────── -->
<!-- Invites lorry owners to register and host on the platform -->
<section style="background: var(--grad-dark); color: white; padding: var(--space-12) 0; text-align: center; border-radius: var(--radius-xl) var(--radius-xl) 0 0;">
    <div class="container">
        <!-- CTA heading -->
        <h2 style="color: white; margin-bottom: var(--space-3);"><i class="fa-solid fa-handshake" style="color: var(--accent);"></i> <?= currentLang() === 'sw' ? 'Unamiliki Lori? Anza Kupata Kipato Leo!' : 'Own a Lorry? Start Earning Today!' ?></h2>
        <!-- Supporting copy -->
        <p style="color: rgba(255,255,255,.8); max-width: 550px; margin: 0 auto var(--space-6);"><?= currentLang() === 'sw' ? 'Jisajili kama mmiliki wa lori, weka magari yako, na uanze kupokea maombi ya uhifadhi kutoka kwa wateja nchini Tanzania.' : 'Register as a lorry owner, list your vehicles, and start receiving verified booking requests from customers across Tanzania.' ?></p>
        <!-- Owner signup button -->
        <a href="<?= APP_URL ?>/auth/register" class="btn btn-accent btn-lg"><i class="fa-solid fa-user-plus"></i> <?= currentLang() === 'sw' ? 'Jisajili kama Mmiliki' : 'Register as Owner' ?></a>
    </div>
</section>
