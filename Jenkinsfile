import groovy.json.JsonOutput

pipeline {
    agent any
    environment {
        PATH = "$WORKSPACE/dkan-tools/bin:$PATH"
        USER = 'jenkins'
        DKTL_SLUG = "dkan-qa-$CHANGE_ID"
        DKTL_TRAEFIK = "proxy"
        WEB_DOMAIN = "ci.civicactions.net"
        DKAN_REPO = 'https://github.com/GetDKAN/dkan.git'
        DKTL_REPO = 'https://github.com/GetDKAN/dkan-tools.git'
        DKTL_DIRECTORY = "$WORKSPACE/dkan-tools"
        DKTL_NO_PROXY = "1"
        TARGET_URL = ""
    }
    stages {
        stage ('Clean-Preclean') {
            when { changeRequest(); }
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
		      
                      docker network disconnect $qa_network_id proxy || true
                      docker network rm $qa_network_id
		      
                    sudo rm -r $WORKSPACE/*
                    fi
                    '''
                    deleteDir()
                }
            }
        }
        stage ('Clone DKAN Repo') {
            when { changeRequest(); }
                steps {
                    dir ("projects/dkan") {
                        git url: DKAN_REPO, branch: "${env.CHANGE_BRANCH}"
                    }
                }
        }
        stage ('Clone dkan-tools') {
            when { changeRequest(); }
                steps {
                    dir ("dkan-tools") {
                        git url: DKTL_REPO, branch: "dkan-qa-builder-no-proxy"
                    }
                }
        }
        stage('Build QA Site') {
            when { changeRequest(); }
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
                        curl -fI `dktl url`
                    '''
                }
            }
        }
    }
    post {
        always {
            script {
                sh '''
                sudo chown -R 1000:docker $WORKSPACE
                '''
            }
        }
        success {
            script {
                gitCommitMessage = sh(returnStdout: true, script: 'git -C projects/dkan log -1 --pretty=%B || true').trim()
                currentBuild.description = "${gitCommitMessage}"
                def target_url = "http://$DKTL_SLUG.$WEB_DOMAIN"
                setBuildStatus("QA site ready at ${target_url}", target_url, "success");
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
            context: "continuous-integration/jenkins/qa-site"
        ]
        def payload = JsonOutput.toJson(data)
        sh "curl -X POST -H 'Content-Type: application/json' -d '${payload}' ${url}"
    }
}
