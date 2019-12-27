import groovy.json.JsonOutput

void createDropsPr(String branch, String version, String date) {
    withCredentials([usernamePassword(credentialsId: 'nucivicmachine', passwordVariable: 'GIT_PASSWORD', usernameVariable: 'GIT_USERNAME')]) {
        def url = "https://api.github.com/repos/GetDKAN/dkan-drops-7/pulls"
        def header = 'Content-Type: application/json'
        def data = [
            title: "${date}: Proposed release for ${version}",
            head: branch,
            base: "master"
        ]
	    def payload = JsonOutput.toJson(data)
	    sh "curl -v -u ${GIT_USERNAME}:${GIT_PASSWORD} -H ${header} -X POST ${url} -d '${payload}'"
    }
}

pipeline {
    agent {
        docker { image 'getdkan/dkan-docker:php72-cli' }
    }
    environment {
        GITHUB_TOKEN = credentials('dkanuploadassets')
        DATE = sh(script: 'date +%s', returnStdout: true).trim()
    }
    stages {
        stage('Upload assets') {
            when { buildingTag() }
            steps {
                sh "cd .. && mv workspace dkan-${TAG_NAME} && mkdir workspace"
                sh "mv ../dkan-${TAG_NAME} ./"
                sh "mv dkan-${TAG_NAME}/scripts/release-upload-assets.php ./"
                sh "cd dkan-${TAG_NAME} && rm -rf Jenkinsfile docs scripts .circleci .github .git"
                sh "php ./release-upload-assets.php"
            }
        }
        stage('Build DROPS') {
            when { buildingTag() }
            steps {
                withCredentials([usernamePassword(credentialsId: 'nucivicmachine', passwordVariable: 'GIT_PASSWORD', usernameVariable: 'GIT_USERNAME')]) {
                    sh('git clone https://${GIT_USERNAME}:${GIT_PASSWORD}@github.com/GetDKAN/dkan-drops-7 --tags')
                    dir('dkan-drops-7') {
                        sh "git checkout -b proposed_release_for_${TAG_NAME}_${DATE}"
                        sh "git remote add pantheon git://github.com/pantheon-systems/drops-7.git"
                        sh "git fetch pantheon && git merge pantheon/master -X theirs"
                        sh "rm -rf profiles/dkan && mv ../dkan-${TAG_NAME} ./profiles/dkan"
                    }
                    dir('dkan-drops-7/profiles/dkan') {
                        sh "rm -rf .git .gitignore modules/contrib themes/contrib libraries/"
                        sh "drush -y make --no-core --contrib-destination=. drupal-org.make --no-recursion"
                        sh "find . -type f -name .gitignore -exec rm -rf {} \\;"
                    }
                    dir ('dkan-drops-7') {
                        sh "git add . -A && git commit -m '${TAG_NAME} release'"
                        sh "git push https://${GIT_USERNAME}:${GIT_PASSWORD}@github.com/GetDKAN/dkan-drops-7 proposed_release_for_${TAG_NAME}_${DATE}"
                    }
                }
                createDropsPr("proposed_release_for_${TAG_NAME}_${DATE}", TAG_NAME, DATE)
            }
        }
    }
}
