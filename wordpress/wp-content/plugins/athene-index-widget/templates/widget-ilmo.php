<div class="widget widget-ilmo">
    <header class="widget-header">
        <h2><a href="<?php echo $ilmomasiina_url; ?>"><?php echo $title; ?></a></h2>
    </header>
    <div class="widget-content">
        <ul class="ilmo clearfix">
        <?php foreach ($this->limit($entries, $items) as $entry): ?>
            <?php /* FIXME: Horrible, horrible grid hack */ ?>
            <li class="ilmo-entry grid_4 alpha omega">
                <article>
                    <section class="date grid_1 alpha">
                        <h5><?php echo $entry['relevant_date']->format('d.m.') ?></h5>
                    </section>
                    <section class="content grid_3 omega">
                        <header class="title">
                            <a href="<?php echo $entry['url'] ?>"><?php echo $entry['name'] ?></a>
                        </header>
                        <section class="status">
                            <?php echo ucfirst($this->statusToString($entry)) ?>
                            <?php echo $this->getTimeFormat($entry['relevant_date'], $timezone); ?>
                        </section>
                    </section>
                </article>
            </li>
        <?php endforeach; ?> 
        </ul>
    </div>
</div>

