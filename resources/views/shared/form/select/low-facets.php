<fieldset>
  <label><?= $title; ?></label>
  <input name="low_facet_id" id="low_facet_id">
</fieldset>

<script nonce="<?= config('main', 'nonce'); ?>">
  var facet_search = async (props = {}) => {
    var settings = {
      method: 'POST',
      mode: 'cors',
      cache: 'no-cache',
      credentials: 'same-origin',
      headers: {
        'Content-Type': 'application/json'
      },
      redirect: 'follow',
      referrerPolicy: 'no-referrer',
      body: JSON.stringify(props)
    };
    try {
      const fetchResponse = await fetch('/mod/admin/search/select/<?= $type; ?>', settings);
     
      return await fetchResponse.json();
    } catch (e) {
      return e;
    }
  };

  document.addEventListener("DOMContentLoaded", async () => {

    var search_facet = await facet_search();
    var input = document.querySelector('#low_facet_id');
    var options_post = {
      // userInput: false,        // <- отключим пользовательский ввод
      skipInvalid: true, // <- не добавлять повтороно не допускаемые теги
      enforceWhitelist: true, // <- добавлять только из белого списка
      maxTags: 15, // <- ограничим выбор фасетов
      callbacks: {
        "dropdown:show": async (e) => await facet_search(),
      },

      whitelist: search_facet,
    };

    var tagify_post = new Tagify(input, options_post);

    <?php if ($action == 'edit') {
      // Значение тега должно совпадать с форматом подсказки: "путь / Название"
      $lowTags = [];
      foreach ((array)($data['low_arr'] ?? []) as $low) {
        $lowPath = trim((string)($low['facet_path'] ?? ''), '/');
        $lowTags[] = [
          'id'    => (int)($low['id'] ?? 0),
          'value' => ($lowPath !== '' ? $lowPath . ' / ' : '') . (string)($low['value'] ?? ''),
        ];
      }
    ?>
      tagify_post.addTags(<?= json_encode($lowTags, JSON_UNESCAPED_UNICODE | JSON_HEX_APOS | JSON_HEX_TAG) ?>)
    <?php } ?>

  });
</script>