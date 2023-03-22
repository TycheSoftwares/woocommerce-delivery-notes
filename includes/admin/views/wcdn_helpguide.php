<ul class="nav nav-tabs non-bg" id="wcdn_tab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" id="wcdn-filter-tab" data-bs-toggle="tab" data-bs-target="#wcdn_filters" type="button" role="tab" aria-controls="wcdn_filters" aria-selected="true">Filters</button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" id="wcdn-faq-tab" data-bs-toggle="tab" data-bs-target="#wcdn_faq" type="button" role="tab" aria-controls="wcdn_faq" aria-selected="false">FAQ</button>
    </li>
</ul>
<div class="tab-content" id="wcdn_tabContent">
    <div class="tab-pane fade show active" id="wcdn_filters" role="tabpanel" aria-labelledby="wcdn-filter-tab">
        <div class="tab_container">
            <?php include_once 'wcdn_filters.php'; ?>
        </div>
    </div>
    <div class="tab-pane fade" id="wcdn_faq" role="tabpanel" aria-labelledby="wcdn-faq-tab">
        <div class="tab_container">
            <?php include_once 'wcdn_faq.php'; ?>
        </div>
    </div>
</div>