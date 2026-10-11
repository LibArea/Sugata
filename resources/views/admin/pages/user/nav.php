 <div class="nav-bar mt20">
   <ul class="nav scroll-menu">
     <?= insert(
        '/shared/navigation/nav',
        [
          'list' =>  [
            [
              'url'   => url('setting'),
              'title' => 'app.setting',
              'id'    => 'settings',
            ],
            [
              'url'   => url('setting.security'),
              'title' => 'app.password',
              'id'    => 'security',
            ],
          ],

          'sheet' =>  $sheet
        ],
      );
      ?>
   </ul>
 </div>