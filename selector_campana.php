<?php if (count($disponibles) > 1): ?>
    <form method="get">
        Campaña:
        <select name="campana_id" onchange="this.form.submit()">
            <?php foreach ($disponibles as $c): ?>
                <option value="<?= $c['id'] ?>" <?= $c['id'] == $campana['id'] ? 'selected' : '' ?>><?= h($c['nombre']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>
<?php else: ?>
    <p>Campaña: <b><?= h($campana['nombre']) ?></b></p>
<?php endif; ?>
