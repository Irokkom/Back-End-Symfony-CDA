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
APP_SECRET=a67f452a7c0cdcb2e2f58f4cbb6f5c82
DATABASE_URL=mysql://root:routitop@127.0.0.1:3306/${DEPLOY_DIR}?serverVersion=8.3.0&charset=utf8mb4
                    """.stripIndent()

                    writeFile file: "${DEPLOY_DIR}/.env.local", text: envLocal
                    
                    // Vérifier que le fichier a bien été créé
                    sh "cat ${DEPLOY_DIR}/.env.local"
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
                // Nouvelle étape SonarQube Analysis
        // stage('Analyse SonarQube') {
        //     steps {
                  // Le nom 'MySonarQubeServer' doit correspondre à celui configuré dans Manage Jenkins > Configure System
        //         withSonarQubeEnv('MySonarQubeServer') {
        //             dir("${DEPLOY_DIR}") {
        //                 sh """
        //                     sonar-scanner \
        //                     -Dsonar.projectKey=mon_projet_symfony \
        //                     -Dsonar.projectName="MonProjet Symfony" \
        //                     -Dsonar.projectVersion=1.0 \
        //                     -Dsonar.sources=src \
        //                     -Dsonar.exclusions=vendor/**,tests/** \
        //                     -Dsonar.host.url=http://localhost:9000 \
        //                     -Dsonar.login=${SONARQUBE_TOKEN} \
        //                     -Dsonar.password=
        //                 """
        //             }
        //         }
        //     }
        // }

        stage('Déploiement') {
            steps {
                sh "rm -rf /var/www/html/${DEPLOY_DIR}" // Supprime le dossier de destination
                sh "mkdir /var/www/html/${DEPLOY_DIR}" // Recréé le dossier de destination
                sh "cp -rT ${DEPLOY_DIR} /var/www/html/${DEPLOY_DIR}"
                sh "chmod -R 775 /var/www/html/${DEPLOY_DIR}/var"
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
