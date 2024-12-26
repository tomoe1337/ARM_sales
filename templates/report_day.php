<?php
    require_once("../vendor/reports.php")
?>

<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>№</th>
                    <th>Имя покупателя</th>
                    <th>Сумма сделки</th>
                    <th>Продукт</th>
                    <th>Контакты покупателя</th>
                    <th>Дата сделки</th>
                    <th>№ менеджера</th>




                </tr>
            </thead>
            <tbody>

            <?php   

                while ($data = mysqli_fetch_assoc($day_deals)) {
                    ?>

                <tr>
                    <td><?=htmlspecialchars($data['id'])?></td>
                    <td><?=htmlspecialchars($data['full_name'])?></td>
                    <td><?=htmlspecialchars($data['amount'])?></td>
                    <td><?=htmlspecialchars($data['product'])?></td>
                    <td><?=htmlspecialchars($data['email'])?></td>
                    <td><?=htmlspecialchars($data['date'])?></td>
                    <td>
                        <center><?=htmlspecialchars($data['manager_id'])?></center>                      
                    </td>

                </tr>
            <?php
                } 
            ?>

            </tbody>
        </table>
    </div>
    
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>

</body>