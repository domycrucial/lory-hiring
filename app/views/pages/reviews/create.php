<div class="auth-wrapper">
    <div class="auth-card" style="max-width: 500px; width: 100%;">
        <h2 class="mb-2"><i class="fa-solid fa-star" style="color: var(--warning);"></i> Rate Your Trip</h2>
        <p class="text-muted mb-6">Leave feedback for your booking <strong><?= e($booking['booking_ref']) ?></strong>.</p>

        <form action="<?= APP_URL ?>/bookings/<?= (int)$booking['id'] ?>/review" method="POST">
            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">
            <input type="hidden" name="rating" id="rating_value" value="5">

            <!-- Star Picker -->
            <div class="form-group mb-4 text-center">
                <label class="form-label mb-2" style="display: block;">How was your overall experience?</label>
                <div class="star-rating-selector" style="font-size: 2.25rem; display: inline-flex; gap: 8px; justify-content: center; cursor: pointer;">
                    <i class="fa-solid fa-star star-btn" data-value="1" style="color: var(--warning);"></i>
                    <i class="fa-solid fa-star star-btn" data-value="2" style="color: var(--warning);"></i>
                    <i class="fa-solid fa-star star-btn" data-value="3" style="color: var(--warning);"></i>
                    <i class="fa-solid fa-star star-btn" data-value="4" style="color: var(--warning);"></i>
                    <i class="fa-solid fa-star star-btn" data-value="5" style="color: var(--warning);"></i>
                </div>
            </div>

            <!-- Comments -->
            <div class="form-group mb-6">
                <label for="review_comment" class="form-label">Review Details / Maoni</label>
                <textarea id="review_comment" name="comment" rows="4" class="form-control" placeholder="Tell other customers about the driver service, lorry quality, and overall experience..." required></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-full">
                <i class="fa-solid fa-paper-plane"></i> Submit Review
            </button>
            <a href="<?= APP_URL ?>/bookings/mine" class="btn btn-outline w-full mt-2 text-center">Cancel</a>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const stars = document.querySelectorAll('.star-btn');
    const ratingInput = document.getElementById('rating_value');

    stars.forEach(star => {
        star.addEventListener('click', function() {
            const val = parseInt(this.getAttribute('data-value'));
            ratingInput.value = val;
            
            // Toggle filled / empty stars
            stars.forEach(s => {
                const sVal = parseInt(s.getAttribute('data-value'));
                if (sVal <= val) {
                    s.classList.remove('fa-regular');
                    s.classList.add('fa-solid');
                    s.style.color = 'var(--warning)';
                } else {
                    s.classList.remove('fa-solid');
                    s.classList.add('fa-regular');
                    s.style.color = 'var(--gray-300)';
                }
            });
        });
    });
});
</script>
