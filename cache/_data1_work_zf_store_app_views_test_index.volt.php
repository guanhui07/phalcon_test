test

<!--
https://olddocs.phalconphp.com/en/3.0.0/reference/volt.html
-->

<p>
    <?php echo $this->tag->submitButton("Register"); ?>
    <?= trim($postId) ?><?= 'test' ?>

</p>


<?php if ($show_navigation) { ?>
            <ul id="navigation">
                <?php foreach ($menu as $key => $item) { ?>
                    <li>
                        <a href="<?= $item['href'] ?>">
                            <?= $item['caption'] ?>
                        </a>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>

        <h1><?= $post['title'] ?></h1>

        <div class="content">
            <?= $post['content'] ?>
        </div>
<?php $test1 = trim('test'); ?>

<?= $test1 ?>
<?php $decoded = json_decode('{"one":11,"two":2,"three":3}', 1); ?>
<?= $decoded['one'] ?>




<?php $numbers = ['one' => 'one1', 'two' => 'two', 'three' => 3]; ?>

<?php foreach ($numbers as $name => $value) { ?>
    <?php if ($value != 'two') { ?>
     <?= $name ?> => <?= $value ?> <br />
     <?php } elseif (1 == 1) { ?>
        <br /> <hr />
     <?php } ?>
<?php } ?>

<?= $numbers['two'] ?>

<?php if (0 || 1) { ?>
<br />ok

<?php } ?>


<?php if (2 && 1) { ?>
<br />ok !

<?php } ?>

<?php if ((!0)) { ?>
<br />ok 0!

<?php } ?>


<?= 'hello ' . 'world' ?>



<?php if ($this->isIncluded('a', 'abc')) { ?>
<br />ok strpos!

<?php } ?>


<?php $at1 = ('a' ? '<br />' . 'isset b' . '<br />' : 'c'); ?>


<?php if ((isset($at1))) { ?>
<?= $at1 ?>
<?php } ?>


<?php $external = false; ?>
<?php if (gettype($external) === ('boolean')) { ?>
    <?= 'external is false or true' ?>
<?php } ?>


<?= $this->length($at1) ?>

