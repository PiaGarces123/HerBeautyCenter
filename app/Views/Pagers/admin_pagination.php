<?php
/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2);
?>

<nav aria-label="Navegación de páginas">
    <ul class="pagination pagination-sm justify-content-center mb-0" style="gap: 0.25rem;">
        <?php if ($pager->hasPrevious()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getPrevious() ?>" class="page-link" aria-label="Anterior" style="border-radius: 0.5rem; color: #d6858e; border-color: #d6858e;">
                    <span aria-hidden="true" class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle;">chevron_left</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" style="border-radius: 0.5rem; background-color: #f8f9fa;">
                    <span aria-hidden="true" class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle;">chevron_left</span>
                </span>
            </li>
        <?php endif ?>

        <?php foreach ($pager->links() as $link) : ?>
            <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
                <a href="<?= $link['uri'] ?>" class="page-link" style="border-radius: 0.5rem; <?= $link['active'] ? 'background-color: #d6858e; border-color: #d6858e;' : 'color: #d6858e; border-color: #d6858e;' ?>">
                    <?= $link['title'] ?>
                </a>
            </li>
        <?php endforeach ?>

        <?php if ($pager->hasNext()) : ?>
            <li class="page-item">
                <a href="<?= $pager->getNext() ?>" class="page-link" aria-label="Siguiente" style="border-radius: 0.5rem; color: #d6858e; border-color: #d6858e;">
                    <span aria-hidden="true" class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle;">chevron_right</span>
                </a>
            </li>
        <?php else : ?>
            <li class="page-item disabled">
                <span class="page-link" style="border-radius: 0.5rem; background-color: #f8f9fa;">
                    <span aria-hidden="true" class="material-symbols-outlined" style="font-size: 1rem; vertical-align: middle;">chevron_right</span>
                </span>
            </li>
        <?php endif ?>
    </ul>
</nav>
