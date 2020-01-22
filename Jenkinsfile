import groovy.json.JsonOutput

/**
 * Create a pull request on DKAN-DROPS-7 against a branch.
 *
 * @param version The tag in DKAN we're building against
 * @param date A timestamp for the new version
 * @param username The username for github.
 * 
 */
void createDropsPr(String version, String date, String username) {
    withCredentials([string(credentialsId: 'dkanuploadassets', variable: 'GITHUB_TOKEN')]) {
        def branch = "proposed_release_for_${TAG_NAME}_${DATE}"
        def url = "https://api.github.com/repos/GetDKAN/dkan-drops-7/pulls"
        def header = 'Content-Type: application/json'
        def data = [
            title: "${date}: Proposed release for ${version}",
            head: branch,
            base: "master"
        ]
	def payload = JsonOutput.toJson(data)
	sh "curl -v -u ${username}:${GITHUB_TOKEN} -H ${header} -X POST ${url} -d '${payload}'"
    }
}

/**
 * Release tasks for DKAN.
 *
 * Create tarballs with version info and without dev artifacts,
 * and create a PR for a DKAN-DROPS-7 (Pantheon) release.
 *
 * - Uses the GitHub API to create pull requests
 * - Uses Credentials Binding Plugin
 * - Expects a private access token for Github stored in "dkanuploadassets"
 * - Depends on the pantheon-system and GetDKAN/dkan-drops-7 repos
 * - Uses the getdkan/dkan-docker:php72-cli image
 */
pipeline {
    agent {
        docker { image 'getdkan/dkan-docker:php72-cli' }
    }
    environment {
        DATE = sh(script: 'date +%s', returnStdout: true).trim()
        GITHUB_USERNAME = "nucivicmachine"
        GITHUB_TOKEN = credentials('dkanuploadassets')
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
                sh("git clone https://${GITHUB_USERNAME}:${GITHUB_TOKEN}@github.com/GetDKAN/dkan-drops-7 --tags")
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
                    sh "git push https://${GITHUB_USERNAME}:${GITHUB_TOKEN}@github.com/GetDKAN/dkan-drops-7 proposed_release_for_${TAG_NAME}_${DATE}"
                }
                createDropsPr(TAG_NAME, DATE, GITHUB_USERNAME)
            }
        }
    }
}
