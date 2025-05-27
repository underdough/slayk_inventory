<?php
                session_start();
                // Mostrar mensaje de éxito si existe
                if (isset($_SESSION['registro_exitoso'])) {
                    echo '<div class="mensaje-exito">';
                    echo '<p>' . $_SESSION['registro_exitoso'] . '</p>';
                    echo '</div>';
                    unset($_SESSION['registro_exitoso']);
                }
                ?>