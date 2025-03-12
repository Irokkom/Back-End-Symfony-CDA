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
                    sh 'mkdir -p var/sessions/prod var/sessions/dev var/sessions/test'
                    sh 'chmod -R 777 var/sessions'
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
                    sh 'chmod -R 755 public/'
                    sh 'chmod -R 777 var/'
                    sh 'chmod -R 777 public/.htaccess'
                }
            }
        }
        
        stage('Configuration Apache') {
            steps {
                script {
                    // Assurez-vous que le répertoire de déploiement est accessible par Apache
                    sh "chmod -R 755 ${DEPLOY_DIR}"
                    
                    // Créer un fichier VirtualHost pour Apache si nécessaire
                    sh """
                        echo '<VirtualHost *:80>
                            ServerName web019.azure.certif.academy
                            ServerAlias web019.azure.certif.academy
                            DocumentRoot /var/lib/jenkins/workspace/web019/public
                            
                            <Directory /var/lib/jenkins/workspace/web019/public>
                                AllowOverride All
                                Require all granted
                                Options Indexes FollowSymLinks MultiViews
                                FallbackResource /index.php
                            </Directory>
                            
                            # Uncomment the following lines if you install assets as symlinks
                            # or if you experience problems related to symlinks when compiling LESS/Sass/CoffeeScript assets
                            <Directory /var/lib/jenkins/workspace/web019>
                                Options FollowSymLinks
                            </Directory>
                            
                            ErrorLog \${APACHE_LOG_DIR}/web019_error.log
                            CustomLog \${APACHE_LOG_DIR}/web019_access.log combined
                        </VirtualHost>' > /tmp/web019.conf
                        
                        sudo cp /tmp/web019.conf /etc/apache2/sites-available/web019.conf
                        sudo a2ensite web019.conf
                        sudo a2enmod rewrite
                        sudo a2enmod headers
                        sudo systemctl restart apache2
                        
                        # Vérifier les erreurs Apache
                        sudo tail -n 50 /var/log/apache2/error.log
                        sudo tail -n 50 /var/log/apache2/web019_error.log || echo "Le fichier de log n'existe pas encore"
                        
                        # Créer un lien symbolique vers le répertoire public si nécessaire
                        if [ ! -L /var/www/html/web019 ]; then
                            sudo ln -sf /var/lib/jenkins/workspace/web019/public /var/www/html/web019
                        fi
                    """
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
