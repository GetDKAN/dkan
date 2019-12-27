pipeline {
    agent any
    options {
        ansiColor 'xterm'
    }
    // environment {
    //     PATH = "$WORKSPACE/dkan-tools/bin:$PATH"
    //     USER = 'jenkins'
    // }
    stages {
        stage('Setup') {
            when { buildingTag() }
            steps {
              sh "curl -O -L https://github.com/GetDKAN/dkan-tools/archive/master.zip"
              sh "unzip master.zip && mv dkan-tools-master dkan-tools && rm master.zip"
            }
        }
        stage('Upload assets') {
            when { buildingTag() }
            steps {
                sh "ls -la"
            }
        }
    }
}