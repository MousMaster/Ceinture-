-- Initialisation de la base de données pour Registre de Permanence
-- Ce script s'exécute automatiquement au premier démarrage de MySQL

-- Configuration du charset
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- S'assurer que la base utilise utf8mb4
ALTER DATABASE IF EXISTS registre_permanence
    CHARACTER SET = utf8mb4
    COLLATE = utf8mb4_unicode_ci;

-- Accorder tous les privilèges à l'utilisateur
GRANT ALL PRIVILEGES ON registre_permanence.* TO 'registre'@'%';
FLUSH PRIVILEGES;

-- Message de confirmation
SELECT 'Base de données initialisée avec succès' AS message;
