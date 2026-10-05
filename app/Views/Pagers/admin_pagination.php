<?php $pager->setSurroundCount(2) ?>

<!-- 
    Paginador Personalizado del Panel
-->
<nav aria-label="Navegación de páginas">
    <ul class="pagination pagination-sm justify-content-center mb-0 admin-pagination">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getPrevious() ?>" class="page-link admin-page-link" aria-label="Anterior">
                    <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_left</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link admin-page-link--disabled">
                    <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_left</span>
                </span>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a href="<?= $link['uri'] ?>" class="page-link <?= $link['active'] ? 'admin-page-link--active' : 'admin-page-link' ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getNext() ?>" class="page-link admin-page-link" aria-label="Siguiente">
                    <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_right</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link admin-page-link--disabled">
                    <span aria-hidden="true" class="material-symbols-outlined admin-page-icon">chevron_right</span>
                </span>
            </li>
        <?php endif ?>
    </ul>
</nav>
