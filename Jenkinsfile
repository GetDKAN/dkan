/*
On PR, builds QA environment.
On resubmit of the same PR, rebuilds QA environment.
On merge, tears down QA environment.
*/

mport groovy.json.JsonOutput

pipeline {
    agent any
    environment {
        PATH = "$WORKSPACE/dkan-tools/bin:$PATH"
        DKTL_VERSION = '4.1.0' //The latest version causes an error.
        DKTL_SLUG = "dkan$CHANGE_ID"
        DKTL_TRAEFIK = "proxy"
        WEB_DOMAIN = "ci.civicactions.net"
        GITHUB_PROJECT = 'https://github.com/GetDKAN/dkan.git'
    }
    stages {
        stage ('Preclean') {
            when { changeRequest() }
            steps {
                script {
                    sh '''
                    echo "Checking for existing containers"
                    containers_up=`ps -ef|grep ${DKTL_SLUG}`
                    if [ !-z $containers_up ]
                    then
                      echo "Shutting down existing containers"
                      dktl down -r
                    fi
                    echo "Removing existing repos for dkan and dkan-tools"
                    sudo rm -rf dkan*
                    sudo rm -rf dkan-tools*
                    '''
                }
            }
        }
        stage ('Clone Repo') {
            when { changeRequest() }
            steps {
                dir ("dkan") { 
                    git url: GITHUB_PROJECT, branch: GITHUB_BRANCH
                }
            }
        }
        stage('Download dkan-tools') {
            when { changeRequest() }
            steps {
                sh '''
                curl -O -L "https://github.com/GetDKAN/dkan-tools/archive/${DKTL_VERSION}.zip"
                unzip ${DKTL_VERSION}.zip && mv dkan-tools-${DKTL_VERSION} dkan-tools && rm ${DKTL_VERSION}.zip
                '''
            }
        }
        stage('Build site') {
            when { changeRequest() }
            steps {
                dir("dkan") {
                    script {
                        sh '''
                            cd ..
                            dktl dc up -d
                            dktl make
                            dktl install
                            dktl frontend:install
                            dktl frontend:build
                        '''
                    }
                }
            }
        }
        //When merging the PR to master, remove the QA containers
        stage('Drop On Merge') {
            when { changeRequest target: 'master' }
            steps {
                dir("${DKTL_SLUG}") {
                    script {
                        '''
                        cd ..
                        dktl dc down -r
                        '''
                    }
                }
            }
        }
    }
    post {
        success {
            slackSend (color: '#FFFF00', message: "DKAN2 QA Site Build - Success: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]' (${env.BUILD_URL})")
        }
        failure {
            slackSend (color: '#FFFF00', message: "DKAN2 QA Site Build - Failure: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]' (${env.BUILD_URL})")
        }
    }
}