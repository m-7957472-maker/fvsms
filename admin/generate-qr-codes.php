<?php session_start();
include_once(__DIR__ . '/../includes/config.php');
if(strlen( $_SESSION["aid"])==0) {   
    header('location:logout.php');
} else {
<?php
// QR feature removed. Redirecting back to Manage Restock.
session_start();
header('Location: manage-restock.php');
exit;
                            grid.appendChild(card);

                            // Generate QR code
                            new QRCode(qrDiv, {
                                text: product.id,
                                width: 200,
                                height: 200,
                                colorDark: '#000000',
                                colorLight: '#ffffff',
                                correctLevel: QRCode.CorrectLevel.H
                            });
                        });

                        container.appendChild(grid);
                    })
                    .catch(err => alert('Error: ' + err));
            }

            // Generate on page load
            window.addEventListener('load', generateQRCodes);
        </script>
    </body>
</html>
<?php } ?>
