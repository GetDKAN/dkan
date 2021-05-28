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
	TARGET_URL = ""
    }
    stages {
        stage ('Clean-Preclean') {
            steps {
                script {
                    sh '''
                    echo "If exist...remove containers and network for qa_$CHANGE_ID"
                    qa_container_ids=`docker ps -f name=$DKTL_SLUG -q`
                    qa_network_id=`docker network ls -f name=$DKTL_SLUG -q`

                    if [ -n "$qa_container_ids" ]
                    then
                      for i in $qa_container_ids
                      do
                        docker container stop $i
                        docker container rm $i
                      done
		      
		      docker network disconnect $qa_network_id proxy
                      docker network rm $qa_network_id
                    fi
                    '''
                    deleteDir()
                }
            }
        }
        stage ('Clone DKAN Repo') {
            when { allOf { changeRequest(); not { branch '2.x' } } }
                steps {
                    dir ("projects/dkan") {
                        git url: DKAN_REPO, branch: "${env.CHANGE_BRANCH}"
                    }
                }
        }
        stage ('Clone dkan-tools') {
            when { allOf { changeRequest(); not { branch '2.x' } } }
                steps {
                    dir ("dkan-tools") {
                        git url: DKTL_REPO, branch: "dkan-qa-builder"
                    }
                }
        }
        stage('Build QA Site') {
            when { allOf { changeRequest(); not { branch '2.x' } } }
            steps {
                script {
                    sh '''
		    	cd projects
                        export DKTL_DIRECTORY="$WORKSPACE/dkan-tools"
                        echo $DKTL_DIRECTORY
                        dktl init --dkan-local
                        dktl demo
                        dktl drush user:password admin mayisnice
                        sudo chown -R 1000:docker $WORKSPACE/dkan-tools/vendor
                    '''
                }
            }
        }
        stage('Check QA Site') {
            when { allOf { changeRequest(); not { branch '2.x' } } }
            steps {
                script {
                    sh '''
                    QA_SITE_WEB_ID=`docker ps|grep qa_$CHANGE_ID|grep web|awk '{ print $1 }'`
                    QA_SITE_PORT=`docker container port $QA_SITE_WEB_ID|grep 80|awk '{ print $3 }'|awk 'BEGIN { FS = ":" };{ print $2 }'`
                    echo QA site ready at http://$DKTL_SLUG.$WEB_DOMAIN
                    curl -I "http://$DKTL_SLUG.$WEB_DOMAIN"
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
