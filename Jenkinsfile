pipeline {
    agent any
    environment {
        PATH = "$WORKSPACE/dkan-tools/bin:$PATH"
        USER = 'jenkins'
        DKTL_SLUG = "dkan_qa_$CHANGE_ID"
        DKTL_TRAEFIK = "proxy"
        WEB_DOMAIN = "ci.civicactions.net"
        DKAN_REPO = 'https://github.com/GetDKAN/dkan.git'
        DKTL_REPO = 'https://github.com/GetDKAN/dkan-tools.git'
        DKTL_DIRECTORY = "$WORKSPACE/dkan-tools"
    }
    stages {
        stage ('Preclean') {
            when { changeRequest() }
            steps {
                script {
                    sh '''
                    echo "If exist...remove containers and network for qa_$CHANGE_ID"
                    qa_container_ids=`docker ps|grep qa_$CHANGE_ID|awk '{ print $1 }'`
                    qa_network_id=`docker network ls|grep qa_$CHANGE_ID|awk '{ print $1 }'`

                    if [ $(docker ps|grep qa_$CHANGE_ID|awk '{ print $1 }') ]
                    then
                      cd projects/dkan
                      dktl docker:compose stop
                      dktl docker:compose rm -f
                      docker network rm dkan_qa_$CHANGE_ID_default
                    fi
                    '''
                    deleteDir()
                }
            }
        }
        stage ('Clone DKAN Repo') {
                steps {
                    dir ("projects/dkan") {
                        git url: DKAN_REPO, branch: "${env.CHANGE_BRANCH}"
                    }
                }
        }
        stage ('Clone dkan-tools') {
                steps {
                    dir ("dkan-tools") {
                        git url: DKTL_REPO, branch: "master"
                    }
                }
        }
        stage('Build QA Site') {
            when { changeRequest() }
            steps {
                script {
                    sh '''
                        cd projects
                        export DKTL_DIRECTORY="$WORKSPACE/dkan-tools"
                        echo $DKTL_DIRECTORY
                        dktl init --dkan-local
                        dktl demo
                        sudo chown -R 1000:docker /var/jenkins_home/jobs/DKAN/jobs/DKAN/branches/PR-$CHANGE_ID/workspace/dkan-tools/vendor
                    '''
                }
            }
        }
        stage('Check QA Site') {
            when { changeRequest() }
            steps {
                script {
                    def target_url = "http://${DKTL_SLUG}.${WEB_DOMAIN}"
                    setBuildStatus("QA site ready at ${target_url}", target_url, "success");
                }
                sh '''
                echo QA site ready at http://${DKTL_SLUG}.${WEB_DOMAIN}/
                curl "http://${DKTL_SLUG}.${WEB_DOMAIN}"
                '''
            }
        }
        //When merging the PR to master, remove the QA containers
        stage('Drop On Merge') {
            when { changeRequest target: 'master' }
            steps {
                script {
                    '''
                    dktl dc down -r
                    '''
                }
            }
        }
    }
    post {
        success {
            script {
                gitCommitMessage = sh(returnStdout: true, script: 'cd projects/dkan; git log -1 --pretty=%B').trim()
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
    withCredentials([string(credentialsId: 'github-token',
			  variable: 'GITHUB_API_TOKEN')]) {
	def url = "https://api.github.com/repos/getdkan/dkan/statuses/env.GIT_COMMIT?access_token=${GITHUB_API_TOKEN}"
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
