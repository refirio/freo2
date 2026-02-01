            <?php if (!empty($_view['comments'])) : ?>
            <div id="comment">
                <h3 class="h4 mt-4"><?php h($GLOBALS['string']['heading_comment_list']) ?></h3>

                <?php foreach ($_view['comments'] as $comment) : ?>
                <div class="comment">
                    <h4 class="h6 mt-4"><?php if ($comment['url']) : ?><a href="<?php t($comment['url']) ?>" target="_blank"><?php endif ?><?php h($comment['name']) ?><?php if ($comment['url']) : ?></a><?php endif ?>（<time datetime="<?php h(localdate('Y-m-d H:i:s', $comment['created'])) ?>"><?php h(localdate('Y/m/d H:i', $comment['created'])) ?></time>）</h4>
                    <p><?php h($comment['message']) ?></p>
                </div>
                <?php endforeach ?>
            </div>
            <?php endif ?>
