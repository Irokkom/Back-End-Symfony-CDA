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
                        // sh """
                        //     sed -i 's|DATABASE_URL="mysql://ChangeHere:@127.0.0.1:3306/ChangeHere?serverVersion=9.1.0&charset=utf8mb4"|DATABASE_URL="mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4"|' .env
                        //     sed -i 's|APP_ENV=.*|APP_ENV=prod|' .env
                        //     sed -i 's|APP_DEBUG=.*|APP_DEBUG=1|' .env
                        // """
                        
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
        stage('Configuration de l\'environnement de test') {
            steps {
                dir("${DEPLOY_DIR}") {
                    script {
                        def envTestFile = "${DEPLOY_DIR}/.env.test"
                        def dbUrl = "DATABASE_URL=mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4"

                        sh """
                            if [ ! -f .env.test ]; then
                                touch .env.test
                            fi

                            if grep -q '^DATABASE_URL=' .env.test; then
                                sed -i 's|^DATABASE_URL=.*|${dbUrl}|' .env.test
                            else
                                echo '${dbUrl}' >> .env.test
                            fi
                        """
                    }
                }
            }
        }

        stage('Installation des dépendances') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --optimize-autoloader'
                    sh 'composer require symfony/apache-pack'
                }
            }
        }
        
        stage('Migration de la base de production') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=prod'
                }
            }
        }
        
        stage('Nettoyage du cache') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console cache:clear --env=prod'
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
