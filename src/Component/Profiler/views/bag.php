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
                <td><?php echo htmlentities($key) ?></td>
                <td><?php echo htmlentities(trim(json_encode($value, 64 | 256), '[]')) ?></td>
            </tr>
        <?php endforeach ?>
    </tbody>
</table>
