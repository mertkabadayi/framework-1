<table class="pf-table">
    <thead>
        <tr>
            <th>Key</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($parameters as $key => $value) : ?>
            <tr>
                <td><?php echo htmlspecialchars($key, ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8', false) ?></td>
                <td><?php echo htmlspecialchars(trim(json_encode($value, 64 | 256), '[]'), ENT_QUOTES | ENT_SUBSTITUTE, 'utf-8', false) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
