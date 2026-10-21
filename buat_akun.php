<?php

echo "ADMIN: " . password_hash("admin123", PASSWORD_DEFAULT) . "<br><br>";

echo "GURU: " . password_hash("guru123", PASSWORD_DEFAULT) . "<br><br>";

echo "WALI KELAS: " . password_hash("wali123", PASSWORD_DEFAULT);

?>