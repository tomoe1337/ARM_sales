    <div class="list-group">

<?php
    require_once ('../vendor/clients_stats.php');
    $clients_data = get_clients_info(count : 3);

    while ($data = mysqli_fetch_assoc($clients_data)){
?>
        <a href="#" class="list-group-item list-group-item-action">
            <?="Клиент: " . htmlspecialchars($data['full_name']) . " - " . htmlspecialchars($data['status'])?>
        </a>

<?php
    }
?>
    </div>