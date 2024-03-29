<?php if (isset($result)): ?>
<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Výsledek testů</title>

    <style>
        * {
            box-sizing: border-box;
        }

        html, body {
            font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            color: #444;
        }

        hr {
            display: block;
            height: 1px;
            border: 0;
            border-top: 1px solid #ccc;
            margin: 1em 0;
            padding: 0;
        }

        a {
            color: #2e6da4;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        table th {
            background-color: #444;
            color: white;
        }

        table th,
        table td {
            border: 1px #cccccc solid;
            padding: 12px 15px;
        }

        .container {
            margin: 0 auto;
            padding: 30px;
            width: 100%;
            max-width: 1280px;
        }

        .success {
            background-color: #aecc4d;
        }

        .success-text {
            color: #aecc4d;
        }

        .fail {
            background-color: #f26363;
        }

        .fail-text {
            color: #f26363;
        }

        /***** PROGRESS BAR *****/

        .progress-bar-chart {
            border-radius: 15px;
            overflow: hidden;
            border: 2px transparent solid;
            box-shadow: 0 0 0 1px #444;
        }

        .progress-bar-chart::after {
            content: '';
            display: block;
            clear: both;
        }

        .progress-bar-chart .bar {
            float: left;
            height: 26px;
        }

        /***** DIRECTORY LIST *****/

        .dir-list {
            list-style: none;
        }

        .dir-list ul {
            list-style: none;
            border-left: 1px #cccccc solid;
        }

        .dir-list li {
            padding: 10px 0 10px 15px;
        }

        .dir-list li::after {
            content: '';
            display: block;
            clear: both;
        }

        .dir-list li div+ul {
            margin-top: 10px;
        }

        .dir-list .progress-bar-chart {
            width: 50%;
            float: right;
            height: 1.2em;
        }

        .back-to-top {
            padding: 15px;
            background-color: rgba(0,0,0,0.7);
            color: white;
            position: fixed;
            bottom: 0;
            right: 0;
            transition: background-color 300ms;
        }

        .back-to-top:hover {
            background-color: rgba(0,0,0,0.5);
        }
    </style>
</head>
<body>
    <div class="container" id="top">
        <h1>Přehled úspěšnosti testů</h1>
        <hr>
        <?php if ($result['total_count'] === 0): ?>
        <h2>Nebyly nalezeny žádné testy</h2>
        <?php else: ?>
        <?php $percent = round(100*$result['success_count']/$result['total_count']) ?>
        <h2><?= $result['success_count'].'/'.$result['total_count'] ?> úspěšných testů (<?= $percent ?>%)</h2>
        <div class="progress-bar-chart">
            <div class="bar success" style="width: <?= $percent ?>%;">&nbsp;</div>
            <div class="bar fail" style="width: <?= 100 - $percent ?>%;">&nbsp;</div>
        </div>
        <p>&nbsp;</p>
        <hr>
        <h2>Úspěšnosti podle adresářů</h2>
        <p><strong>Kliknutím na adresář zobrazíte detaily jednotlivých testů</strong></p>
        <?php function recurDirList($dir) {
            $percent = round(100*$dir['success_count']/$dir['total_count']);
            $dir_end = explode('/', $dir['dir']);
        ?>
        <ul class="dir-list">
            <li><a href="#<?= $dir['dir_id'] ?>"><?= htmlspecialchars(end($dir_end)) ?></a> <span class="<?= $percent == 100 ? 'success-text' : 'fail-text' ?>"><?= $percent ?>% (<?= $dir['success_count'].'/'.$dir['total_count'] ?>)</span>
                <?php
                    foreach ($dir['subdirs'] as $subdir) {
                        recurDirList($subdir);
                    }
                ?>
            </li>
        </ul>
        <?php } recurDirList($result); ?>
        <hr>
        <h2>Detaily jednotlivých testů</h2>

        <?php
            function recurTestDetails($dir)
            {
        ?>
                <div class="dir-details" id="<?= $dir['dir_id'] ?>">
                    <div class="dir-details-inner">
                        <h3><?= htmlspecialchars($dir['dir']); ?></h3>
                        <?php if (!empty($dir['test_info'])): ?>
                        <table class="tests-details">
                            <thead>
                            <tr>
                                <th>Název testu</th>
                                <th>Detaily chyby</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php foreach($dir['test_info'] as $test): ?>
                            <tr class="<?= $test['success'] ? 'success-text' : 'fail-text' ?>">
                                <td><?= htmlspecialchars($test['name']); ?></td>
                                <td><?= $test['details']; ?></td>
                            </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                        <?php endif; ?>
                    </div>
                    <?php foreach($dir['subdirs'] as $subdir) {
                        recurTestDetails($subdir);
                    } ?>
                </div>
        <?php
            } recurTestDetails($result);
            endif;
        ?>
    </div>
    <a href="#top" class="back-to-top">Zpět nahoru</a>
</body>
</html>
<?php endif; ?>