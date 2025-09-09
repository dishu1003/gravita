<?php
// expects $t
?>
<div class="carousel__item testimonial-card" tabindex="0">
  <div class="testimonial-card__quote-icon">
    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21c3 0 7-1 7-8V5c0-1.25-.756-2.017-2-2H4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2 1 0 1 0 1 1v1c0 1-1 2-2 2s-1 .008-1 1.031V20c0 1 0 1 1 1z"></path><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.757-2.017-2-2h-4c-1.25 0-2 .75-2 1.972V11c0 1.25.75 2 2 2h.75c0 2.25.25 4-2.75 4v3c0 1 0 1 1 1z"></path></svg>
  </div>
  
  <div class="testimonial-card__content">
    <p class="testimonial-card__text"><?php echo nl2br(e($t['content'])); ?></p>
  </div>
  
  <div class="testimonial-card__footer">
    <div class="testimonial-card__avatar">
      <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
    </div>
    <div class="testimonial-card__info">
      <strong class="testimonial-card__name"><?php echo e($t['name']); ?></strong>
      <div class="testimonial-card__rating" aria-label="<?php echo (int)$t['rating']; ?> out of 5 stars">
        <?php for($i = 1; $i <= 5; $i++): ?>
          <span class="star <?php echo ($i <= (int)$t['rating']) ? 'star--filled' : ''; ?>">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?php echo ($i <= (int)$t['rating']) ? 'currentColor' : 'none'; ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
          </span>
        <?php endfor; ?>
      </div>
    </div>
  </div>
</div>