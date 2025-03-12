pipeline {
    agent any

    environment {
        GIT_REPO = "https://github.com/Irokkom/Back-End-Symfony-CDA.git"
        GIT_BRANCH = "main"
        DEPLOY_DIR = 'web019'
    }

    stages {
        stage('Cloner le dépôt') {
            steps {
                sh "rm -rf ${DEPLOY_DIR}" // Nettoyage du précédent build
                sh "git clone -b ${GIT_BRANCH} ${GIT_REPO} ${DEPLOY_DIR}"
            }
        }

        stage('Configuration de l\'environnement') {
            steps {
                script {
                    def envLocal = """
                    APP_ENV=prod
                    APP_DEBUG=1
                    DATABASE_URL=mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4
                    """.stripIndent()

                    writeFile file: "${DEPLOY_DIR}/.env.local", text: envLocal
                    
                    // Créer aussi un fichier .env avec la variable DATABASE_URL pour éviter l'erreur
                    def envFile = """
                    # In all environments, the following files are loaded if they exist,
                    # the latter taking precedence over the former:
                    #
                    #  * .env                contains default values for the environment variables needed by the app
                    #  * .env.local          uncommitted file with local overrides
                    #  * .env.$APP_ENV       committed environment-specific defaults
                    #  * .env.$APP_ENV.local uncommitted environment-specific overrides
                    #
                    # Real environment variables win over .env files.
                    #
                    # DO NOT DEFINE PRODUCTION SECRETS IN THIS FILE NOR IN ANY OTHER COMMITTED FILES.
                    
                    ###> symfony/framework-bundle ###
                    APP_ENV=prod
                    APP_SECRET=votre_secret_ici
                    APP_DEBUG=1
                    ###< symfony/framework-bundle ###
                    
                    ###> doctrine/doctrine-bundle ###
                    DATABASE_URL=mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4
                    ###< doctrine/doctrine-bundle ###
                    """.stripIndent()
                    
                    writeFile file: "${DEPLOY_DIR}/.env", text: envFile
                }
            }
        }
        
        stage('Installation des dépendances') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'composer install --optimize-autoloader --no-scripts'
                    sh 'composer require symfony/apache-pack'
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

        stage('Migration de la base de données') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console doctrine:database:create --if-not-exists --env=prod'
                    sh 'php bin/console doctrine:migrations:migrate --no-interaction --env=prod'
                }
            }
        }

        stage('Nettoyage du cache') {
            steps {
                dir("${DEPLOY_DIR}") {
                    sh 'php bin/console cache:clear --env=prod'
                    sh 'php bin/console cache:warmup'
                    
                    // Créer les répertoires de session et leur donner les bonnes permissions
                    sh 'mkdir -p var/sessions/prod var/sessions/dev var/sessions/test'
                    sh 'chmod -R 777 var/sessions'
                    
                    // Vérifier que le fichier .htaccess est bien présent
                    sh 'ls -la public/'
                    sh 'cat public/.htaccess || echo "ATTENTION: .htaccess manquant!"'
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
                sh "rm -rf /var/www/html/${DEPLOY_DIR}" // Supprime le dossier de destination
                sh "mkdir /var/www/html/${DEPLOY_DIR}" // Recréé le dossier de destination
                sh "cp -rT ${DEPLOY_DIR} /var/www/html/${DEPLOY_DIR}"
                sh "chmod -R 775 /var/www/html/${DEPLOY_DIR}/var"
                
                // S'assurer que les permissions sont correctes pour les sessions
                sh "chmod -R 777 /var/www/html/${DEPLOY_DIR}/var/sessions"
                
                // Vérifier que le fichier .htaccess a bien été copié
                sh "ls -la /var/www/html/${DEPLOY_DIR}/public/"
                
                // Créer un fichier de configuration Apache si nécessaire
                sh '''
                    echo "<VirtualHost *:80>
                        ServerName web019.azure.certif.academy
                        DocumentRoot /var/www/html/web019/public
                        
                        <Directory /var/www/html/web019/public>
                            AllowOverride All
                            Require all granted
                            
                            FallbackResource /index.php
                        </Directory>
                        
                        ErrorLog /var/log/apache2/web019_error.log
                        CustomLog /var/log/apache2/web019_access.log combined
                    </VirtualHost>" > /tmp/web019-vhost.conf
                    
                    echo "IMPORTANT: Si le site n'est pas accessible, copiez manuellement le fichier /tmp/web019-vhost.conf dans /etc/apache2/sites-available/ et activez-le avec a2ensite"
                '''
            }
        }
    }

    post {
        success {
            echo 'Déploiement réussi !'
        }
        failure {
            echo 'Erreur lors du déploiement.'
        }
    }
}
