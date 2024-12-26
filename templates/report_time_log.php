<?php
    require_once("../vendor/report_time_log.php")
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Дата</th>
                    <th>Имя сотрудника</th>
                    <th>Время начала рабочего дня</th>
                    <th>Время конца рабочего дня</th>

                </tr>
            </thead>
            <tbody>

            <?php   

                while ($data = mysqli_fetch_assoc($month_time_log)) {
                    ?>

                <tr>

                    <td><?=htmlspecialchars($data['date'])?></td>
                    <td><?=htmlspecialchars($data['full_name'])?></td>
                    <td><?=htmlspecialchars($data['arrived'])?></td>
                    <td><?=htmlspecialchars($data['left']?? "Не отметился" )?></td>

                </tr>
            <?php
                } 
            ?>

            </tbody>
        </table>
    </div>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>