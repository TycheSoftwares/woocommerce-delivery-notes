<?php 
$faq = WCDN_Component::wcdn_get_faq();
?>
<div class="accordion accordion-flush" id="wcdn_faq">
    <?php
        $i = 1;
        foreach ($faq as $key => $singlefaq) {
            ?>
            <div class="accordion-item">
                <h2 class="accordion-header" id="wcdn_faq_<?php echo $i; ?>">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#wcdn_faq_content_<?php echo $i; ?>" aria-expanded="false" aria-controls="wcdn_faq_content_<?php echo $i; ?>">
                        <?php echo $singlefaq['question']; ?>
                    </button>
                </h2>
                <div id="wcdn_faq_content_<?php echo $i; ?>" class="accordion-collapse collapse" aria-labelledby="wcdn_faq_<?php echo $i; ?>" data-bs-parent="#wcdn_faq">
                    <?php echo $singlefaq['answer']; ?>
                </div>
            </div>
            <?php
            $i++;
        } 
    ?>
</div>