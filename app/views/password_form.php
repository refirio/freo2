    <?php if ($_view['entry']['public'] === 'password' && empty($_SESSION['entry_passwords'][$_view['entry']['id']])) : ?>
    <form action="<?php t(MAIN_FILE) ?><?php t($_view['entry']['type_code'] === 'page' ? '/page/' : '/' . $_view['entry']['type_code'] . '/detail/') ?><?php t($_view['entry']['code']) ?>" method="post">
        <input type="hidden" name="_token" value="<?php t($_view['token']) ?>" class="token">
        <input type="hidden" name="exec" value="password">
        <div class="form-group mb-2">
            <label>パスワード</label>
            <input type="password" name="password" value="" class="form-control">
        </div>
        <div class="form-group mt-4">
            <button type="submit" class="btn btn-primary px-4"><?php h($GLOBALS['string']['button_password']) ?></button>
        </div>
    </form>
    <?php endif ?>
