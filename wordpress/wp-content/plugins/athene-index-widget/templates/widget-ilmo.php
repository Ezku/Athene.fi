<div class="widget widget-ilmo">
    <header class="widget-header">
        <h2><a href="<?php echo $ilmomasiina_url; ?>"><?php echo $title; ?></a></h2>
    </header>
    <div class="widget-content">
        <ul class="ilmo clearfix">
        <?php foreach ($this->limit($entries, $items) as $entry): ?>
            <a href="<?php echo $entry['url'] ?>" class="no-decoration">
            <li class="ilmo-entry <?php echo $entry['state'] ?>">
                <article class="date-indexed clearfix">
                    <section class="date">
                        <h5><?php echo $entry['relevant_date']->format('d.m.') ?></h5>
                    </section>
                    <section class="content">
                        <header class="title">
                            <?php echo $entry['name'] ?>
                        </header>
                        <section class="status">
                            <?php echo ucfirst($this->statusToString($entry)) ?>
                            <?php echo $this->getTimeFormat($entry['relevant_date'], $timezone); ?>
                        </section>
                    </section>
                </article>
            </li>
            </a>
        <?php endforeach; ?> 
        </ul>
    </div>
</div>

