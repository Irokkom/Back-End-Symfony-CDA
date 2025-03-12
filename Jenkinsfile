pipeline {
    agent any
    
    environment {
        DEPLOY_DIR = 'web019'
    }
    
    stages {
        stage('Cloner le dépôt') {
            steps {
                sh 'rm -rf ${DEPLOY_DIR}'
                sh 'git clone -b main https://github.com/Irokkom/Back-End-Symfony-CDA.git ${DEPLOY_DIR}'
            }
        }
        
        stage('Configuration de l\'environnement') {
            steps {
                dir("${DEPLOY_DIR}") {
                    script {
                        // Modifier directement le fichier .env pour éviter les problèmes de priorité
                        sh """
                            sed -i 's|DATABASE_URL="mysql://ChangeHere:@127.0.0.1:3306/ChangeHere?serverVersion=9.1.0&charset=utf8mb4"|DATABASE_URL="mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4"|' .env
                            sed -i 's|APP_ENV=.*|APP_ENV=prod|' .env
                            sed -i 's|APP_DEBUG=.*|APP_DEBUG=1|' .env
                        """
                        
                        // Créer également un .env.local pour plus de sécurité
                        sh """
                            echo 'APP_ENV=prod' > .env.local
                            echo 'APP_DEBUG=1' >> .env.local
                            echo 'DATABASE_URL="mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4"' >> .env.local
                        """
                    }
                }
            }
        }

        stage('Installation des dépendances') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --optimize-autoloader'
                }
            }
        }

        stage('Configuration de l\'environnement de test') {
            steps {
                dir("${DEPLOY_DIR}") {
                    script {
                        // Créer un fichier .env.test avec les informations de connexion pour les tests
                        sh """
                            echo 'APP_ENV=test' > .env.test
                            echo 'KERNEL_CLASS=App\\\\Kernel' >> .env.test
                            echo 'APP_SECRET=\$ecretf0rt3st' >> .env.test
                            echo 'SYMFONY_DEPRECATIONS_HELPER=999999' >> .env.test
                            echo 'PANTHER_APP_ENV=panther' >> .env.test
                            echo 'PANTHER_ERROR_SCREENSHOT_DIR=./var/error-screenshots' >> .env.test
                            echo 'DATABASE_URL="mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}_test?serverVersion=8.3.0&charset=utf8mb4"' >> .env.test
                        """
                    }
                }
            }
        }

        stage('Reset de la base') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:database:drop --if-exists --force --env=prod'
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=prod'
                    
                    // Créer aussi la base de test
                    sh 'php bin/console doctrine:database:drop --if-exists --force --env=test'
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=test'
                }
            }
        }

        stage('Migration de la base de données') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=prod'
                    
                    // Migrer aussi la base de test
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=test'
                }
            }
        }
        
        stage('Nettoyage du cache') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console cache:clear --env=prod'
                    sh 'php bin/console cache:clear --env=test'
                }
            }
        }
        
        stage('Tests') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/phpunit tests'
                }
            }
        }

        stage('Déploiement') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console assets:install --env=prod'
                }
            }
        }
    }
    
    post {
        failure {
            echo 'Erreur lors du déploiement.'
        }
        success {
            echo 'Déploiement réussi !'
        }
    }
}
