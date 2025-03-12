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
        
        stage('Correction des tests') {
            steps {
                dir("${DEPLOY_DIR}") {
                    // Déplacer les tests dans le bon répertoire selon PSR-4
                    sh '''
                        mkdir -p tests/Controller tests/Service tests/Integration
                        
                        if [ -f tests/HomeControllerTest.php ]; then
                            mv tests/HomeControllerTest.php tests/Controller/
                        fi
                        
                        if [ -f tests/MongoDBServiceTest.php ]; then
                            mv tests/MongoDBServiceTest.php tests/Service/
                        fi
                        
                        if [ -f tests/HomeControllerMongoDBIntegrationTest.php ]; then
                            mv tests/HomeControllerMongoDBIntegrationTest.php tests/Integration/
                        fi
                    '''
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
