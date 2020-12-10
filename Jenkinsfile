import groovy.json.JsonOutput

def getGitBranchName() {
    return scm.branches[0].name
}

pipeline {
    agent any
    options {
        ansiColor 'xterm'
        skipDefaultCheckout(true)
    }
    environment {
        PATH = "$WORKSPACE/dkan-tools/bin:$PATH"
        USER = 'jenkins'
        DKTL_VERSION = '4.1.0' //The latest version causes a composer error.
        DKTL_SLUG = "dkan$CHANGE_ID"
        DKTL_TRAEFIK = "proxy"
        WEB_DOMAIN = "ci.civicactions.net"
    }
    stages {
        stage('Display Build Details') {
          steps {
              script {
                  repo_name = env.GIT_URL.replaceFirst(/^.*\/([^\/]+?).git$/, '$1')
                  branch_name = getGitBranchName()
                  echo "Branch name: ${branch_name}"
                  echo "Repo name: ${repo_name}"
                  echo "Container name prefix: ${repo_name}_${DKTL_SLUG}_1"
              }
          }
        }
        stage('Setup environment') {
            when { changeRequest() }
            steps {
                script {
                    try {
                        sh '''
                        containers_up=`ps -ef|grep ${DKTL_SLUG}`
                        if [ !-z $containers_up ]
                        then
                          dktl down -r
                        fi
                        '''
                    } catch (err) {
                        echo "DKTL not present; skipping"
                    }
                }
                sh "rm -rf *"
                dir("dkan") {
                    checkout scm
                }
                sh "curl -O -L https://github.com/GetDKAN/dkan-tools/archive/${DKTL_VERSION}.zip"
                sh "unzip ${DKTL_VERSION}.zip && mv dkan-tools-${DKTL_VERSION} dkan-tools && rm ${DKTL_VERSION}.zip"
            }
        }
        stage('Build site') {
            when { changeRequest() }
            environment {
              DKTL_SLUG=
            }
            steps {
                dir("${DKTL_SLUG}") {
                    script {
                        sh '''
                            cd ..
                            dktl init
                            dktl make
                            docker exec -it ${DKTL_SLUG}_cli_1 drush site:install standard --site-name \"Rhode Island\ -y"
                            docker exec -it ${DKTL_SLUG}_cli_1 drush en dkan config_update_ui -y
                            dktl frontend:get
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
}