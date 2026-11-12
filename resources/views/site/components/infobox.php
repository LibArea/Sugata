<?php
/*
 * Инфобокс записи (правая колонка статьи).
 * Данные берутся из экземпляра шаблона (template_instances),
 * при его отсутствии — прежнее поведение: обложка записи + служебные поля.
 *
 * Ожидает: $item, $mod, $toc (array|null)
 */

$mod   = $mod ?? 'static';
$toc   = $toc ?? [];

$itemId   = (int)($item['item_id'] ?? 0);
$instance = $itemId ? \App\Models\TemplateModel::instance($itemId) : null;

$image = '';
$custom = [];

if ($instance) {
    $values = $instance['values'] ?? [];

    $image = trim((string)($values['image'] ?? ''));

    foreach ($instance['fields'] as $field) {
        $key = (string)$field['field_key'];
        if ($key === 'image') {
            continue;
        }

        $value = trim((string)($values[$key] ?? ''));
        if ($value === '') {
            continue;
        }

        $custom[] = [
            'key'   => $key,
            'id'    => (int)($field['field_id'] ?? 0),
            'label' => (string)$field['field_label'],
            'type'  => (string)($field['field_type'] ?? 'text'),
            'value' => $value,
        ];
    }
} else {
    $image = !empty($item['item_thumb_img'])
        ? Img::PATH['thumbs'] . $item['item_thumb_img']
        : (string)Parser::miniature((string)($item['item_content'] ?? ''));
}

$image = trim((string)$image);

$hasKey = static function (array $rows, array $keys): bool {
    foreach ($rows as $row) {
        if (in_array($row['key'], $keys, true)) {
            return true;
        }
    }
    return false;
};
?>

<aside class="side">
  <div class="inbox">
    <div class="inbox__cap"><?= htmlEncode($item['item_title']); ?></div>

    <?php if ($image !== '') : ?>
      <a class="inbox__img" href="<?= htmlEncode($image); ?>">
        <img src="<?= htmlEncode($image); ?>" alt="<?= htmlEncode($item['item_title']); ?>" loading="lazy">
      </a>
    <?php endif; ?>

    <table>
      <?php foreach ($custom as $row) : ?>
        <tr>
          <th><?= htmlEncode($row['label']); ?></th>
          <td>
            <?php if (($row['type'] ?? '') === 'link' && preg_match('~^(https?://|/)~u', (string)$row['value'])) : ?>
              <a href="<?= htmlEncode($row['value']); ?>"><?= htmlEncode($row['value']); ?></a>
            <?php elseif (($row['type'] ?? '') === 'date') : ?>
              <?= htmlEncode(preg_match('/^\d{4}-\d{2}-\d{2}/', (string)$row['value']) ? date('d.m.Y', strtotime((string)$row['value'])) : (string)$row['value']); ?>
            <?php elseif (($row['type'] ?? '') === 'select') : ?>
              <?php
              $optionLabel = \App\Models\TemplateModel::optionsMap((int)($row['id'] ?? 0))[(string)$row['value']] ?? (string)$row['value'];
              ?>
              <?= htmlEncode($optionLabel); ?>
            <?php else : ?>
              <?= htmlEncode($row['value']); ?>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>

      <?php if (!$hasKey($custom, ['category', 'razdel'])) : ?>
        <tr>
          <th><?= __('app.category'); ?></th>
          <td><?= Html::facetDir($item['facet_list'] ?? '', $mod); ?></td>
        </tr>
      <?php endif; ?>

      <?php if (!$hasKey($custom, ['published'])) : ?>
        <tr>
          <th><?= __('app.published'); ?></th>
          <td><?= langDate($item['item_date']); ?></td>
        </tr>
      <?php endif; ?>

      <?php if (!empty($item['item_modified']) && !$hasKey($custom, ['updated'])) : ?>
        <tr>
          <th><?= __('app.updated'); ?></th>
          <td><?= langDate($item['item_modified']); ?></td>
        </tr>
      <?php endif; ?>

      <?php if ($toc && !$hasKey($custom, ['sections'])) : ?>
        <tr>
          <th><?= __('app.sections'); ?></th>
          <td><?= count($toc); ?></td>
        </tr>
      <?php endif; ?>
    </table>
  </div>
</aside>

<?php if ($instance) :
    $ldType = match ((string)($instance['template_code'] ?? '')) {
        'person'    => 'Person',
        'site'      => 'WebSite',
        'term'      => 'DefinedTerm',
        'monastery' => 'Place',
        default     => 'Thing',
    };

    $dirParts  = preg_split('/(@)/', (string)($item['facet_list'] ?? ''));
    $facetPath = trim((string)($dirParts[2] ?? ''), '/');
    $itemUrl   = ($facetPath !== '' && !empty($item['item_slug']))
        ? config('general', 'url_html') . '/' . $facetPath . '/' . $item['item_slug'] . '.html'
        : '';

    $ld = [
        '@context' => 'https://schema.org',
        '@type'    => $ldType,
        'name'     => (string)($item['item_title'] ?? ''),
    ];

    if ($image !== '') {
        $ld['image'] = $image;
    }

    $description = Parser::noHTML((string)($item['item_content'] ?? ''), 200);
    if ($description !== '') {
        $ld['description'] = $description;
    }

    if ($ldType === 'WebSite' && !empty($values['url'])) {
        $ld['url'] = (string)$values['url'];
    } elseif ($itemUrl !== '') {
        $ld['url'] = $itemUrl;
    }

    $sourceUrl = trim((string)($item['item_source_url'] ?? ''));
    if ($sourceUrl !== '') {
        $ld['sameAs'] = $sourceUrl;
    }

    $properties = [];
    foreach ($custom as $row) {
        if ($row['key'] === 'image') {
            continue;
        }
        $properties[] = [
            '@type' => 'PropertyValue',
            'name'  => $row['label'],
            'value' => $row['value'],
        ];
    }
    if ($properties) {
        $ld['additionalProperty'] = $properties;
    }
    ?>
    <script type="application/ld+json"><?= json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?></script>
<?php endif; ?>
