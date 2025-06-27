class AssignedExam{
    constructor(){
        this.examContainer = document.querySelector('.assigned-exam-container')
        if(this.examContainer){
            this.examStatus = this.examContainer.dataset.status
            this.examDuration = this.examContainer.dataset.duration.split(':')
        }

        if(this.examStatus === 'assigned'){
            this.allQuizBlocks = document.querySelectorAll('.quiz-answers')
            this.displayElement = document.getElementById('answered-count')
            this.answeredQuestions = new Set()
            this.answerCount = 0
            this.displayElement.textContent = `0 / ${this.allQuizBlocks.length}`
            
            if(this.examDuration){
                this.hours = parseInt(this.examDuration[0])
                this.minutes = parseInt(this.examDuration[1])
            }
            this.timer = null
            this.timeDisplay = document.getElementById('timer')

            this.examSubmitBtn = document.getElementById('exam-submit')
            this.initTimer()
            this.events()
        }
    }

    events(){
        
        this.allQuizBlocks.forEach(block => {
            const inputs = block.querySelectorAll('input[type="checkbox"], input[type="radio"]')
            if(inputs.length === 0) return

            const nameAttr = inputs[0].getAttribute('name')
            const questionId = nameAttr.replace(/\[\]$/, '')

            inputs.forEach(input => {
                input.addEventListener('change', () => this.updateAnswerStatus(block, questionId))
            })
        })

    }

    updateAnswerStatus(block, questionId){

        const checkedInputs = block.querySelectorAll('input:checked')
        const isAnswers = checkedInputs.length > 0

        if(isAnswers > 0 && !this.answeredQuestions.has(questionId)){
            this.answeredQuestions.add(questionId)
            this.answerCount++
        }
        else if(!isAnswers && this.answeredQuestions.has(questionId)){
            this.answeredQuestions.delete(questionId)
            this.answerCount--
        }

        this.displayElement.textContent = `${this.answerCount} / ${this.allQuizBlocks.length}`
    
    }

    initTimer(){
            
        if(this.hours === 0 && this.minutes === 0){
            this.timeDisplay.textContent = 'Click submit button when you finish the exam'
        }else{
            this.timeDisplay.textContent = this.hours + 'h ' + this.minutes + ' min left'
            this.timer = setInterval(this.updateTimer.bind(this), 100);
        }
    }

    updateTimer(){

        this.minutes--

        if(this.hours === 0 && this.minutes === 0){
            this.examSubmitBtn.click()
            clearInterval(this.timer)
        }else if(this.minutes === 0){
            this.minutes = 59
            this.hours--
        }

        const formattedMinutes = String(this.minutes).padStart(2, '0')
        const formattedHours = String(this.hours).padStart(2,'0')

        this.timeDisplay.textContent = `${formattedHours} h ${formattedMinutes}min left`
    }
}

export default AssignedExam