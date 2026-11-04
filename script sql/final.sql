SELECT * FROM adotapet_db.animais;

ALTER TABLE `animais` 
  DROP COLUMN `foto_url`,
  ADD COLUMN `foto` VARCHAR(255) DEFAULT NULL AFTER `carteira_vacinacao`;