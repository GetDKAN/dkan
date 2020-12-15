import groovy.json.JsonOutput

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
                dir ("dkan") {
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
        }
        stage ('Clone DKAN Repo') {
            when { changeRequest() }
            steps {
                dir ("dkan") { 
                    git url: GITHUB_PROJECT, branch: "${env.CHANGE_BRANCH}"
                }
            }
        }
        stage('Clone dkan-tools') {
            when { changeRequest() }
            steps {
                sh '''
                curl -O -L "https://github.com/GetDKAN/dkan-tools/archive/${DKTL_VERSION}.zip"
                unzip ${DKTL_VERSION}.zip && mv dkan-tools-${DKTL_VERSION} dkan-tools && rm ${DKTL_VERSION}.zip
                '''
            }
        }
        stage('Build QA Site') {
            when { changeRequest() }
            steps {
                dir("dkan") {
                    script {
                        sh '''
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
        stage('Check QA Site') {
            when { changeRequest() }
            steps {
                dir("dkan") {
                    script {
                        def target_url = "http://${DKTL_SLUG}.${WEB_DOMAIN}"
                        setBuildStatus("QA site ready at ${target_url}", target_url, "success");
                    }
                    sh '''
                    echo QA site ready at http://${DKTL_SLUG}.${WEB_DOMAIN}/
                    curl `dktl docker:url`
                    '''
                }
            }
        }
        //When merging the PR to master, remove the QA containers
        stage('Drop On Merge') {
            when { changeRequest target: 'master' }
            steps {
                dir("dkan") {
                    script {
                        '''
                        dktl dc down -r
                        '''
                    }
                }
            }
        }
    }
    post {
        success {
            script {
                gitCommitMessage = sh(returnStdout: true, script: 'git log -1 --pretty=%B').trim()
                currentBuild.description = "${gitCommitMessage}"
            }
        }
    }
}

/**
 * Report build status to github.
 *
 * @param message Message for status description
 * @param target_url URL of the QA site we're building
 * @param state State to report to Github (e.g. "success")
 */
void setBuildStatus(String message, String target_url, String state) {
    withCredentials([string(credentialsId: 'dkanuploadassets',
			  variable: 'GITHUB_API_TOKEN')]) {
	def url = "https://api.github.com/repos/getdkan/dkan/statuses/$GIT_COMMIT?access_token=${GITHUB_API_TOKEN}"
	def data = [
	    target_url: target_url,
	    state: state,
	    description: message,
	    context: "continuous-integration/jenkins/build-status"
	]
	def payload = JsonOutput.toJson(data)
	sh "curl -X POST -H 'Content-Type: application/json' -d '${payload}' ${url}"
    }
}
