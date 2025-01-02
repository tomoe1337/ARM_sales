 <?php
                if (!$working_status){?>
                    <a href="../vendor/working_start.php">
                        <button class="btn btn-primary btn-lg mb-3">Начать смену</button>
                    <a>

            <?php
                    if (isset($_SESSION["message"])){
                        ?>

                        <center>
                                <div id = 'message' class='alert alert-success' role='alert'>
                                    <?=$_SESSION["message"];?>
                                </div>
                        </center>
                        <style>
                            .pressStart{
                                display:none;
                            }
                        </style>


            <?php       unset($_SESSION["message"]);
                    }      
            ?>

                    <center>
                        <div id = 'pressStart' class="alert alert-primary pressStart" role="alert">
                            Начните смену чтобы получить доступ к иструментам
                        </div>
                    </center>
                     
            <?php 
                }if ($working_status){?>
    
                    <button class="btn btn-danger btn-lg mb-3 " data-bs-toggle="modal" data-bs-target="#stopModal">
                        Закончить смену
                    </button>


            <?php   
                    $modal_message = "Вы действительно ходите закончить смену?";
                    $modal_action_link = "../vendor/working_stop.php";
                    require_once('modal.php');

                    if (isset($_SESSION["message"])){
                        ?><center>
                                <div id = 'message' class='alert alert-success' role='alert'>
                                    <?=$_SESSION["message"];?>
                                </div>
                            </center>
                        <?php 
                        unset($_SESSION["message"]);
                    }
                }else {
            ?>       <script>
                        $("#pressStart").delay(3100).show(1000);
                        $("#message").delay(1000).slideUp(2000, function() { $(this).remove(); });
                    </script>
            <?php
                    die();
            }
            ?>
