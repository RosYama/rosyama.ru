<?php

// 000002
// добавить тип дефекта "снег"

global $DB;
$DB->Query("alter table `b_holes` modify column `TYPE` ENUM('badroad','holeonroad','hatch','crossing','nomarking','rails','policeman','fence','holeinyard','light','snow') CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT 'holeonroad'");

?>