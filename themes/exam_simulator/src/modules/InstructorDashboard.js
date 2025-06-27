class InstructorDashboard{
    constructor(){

        this.dashboardSection = document.querySelector(".dashboard-section")

        if(this.dashboardSection){
            this.instId = this.dashboardSection.dataset.userid

            this.addExambtn = document.querySelector(".add-exam")
            this.createForm = document.querySelector(".create-exam-form")
            this.assignMultipleExamsBtn = document.getElementById("assign_multiple_exams")
            
            this.events()
        }
    }

    events(){
        if(this.addExambtn){
            this.addExambtn.addEventListener('click', () => {
                this.createForm.classList.toggle('hidden')
            })
        }
        if(this.assignMultipleExamsBtn){
            this.assignMultipleExamsBtn.addEventListener('click', () => this.createAssignPost())
        }
    }

    createAssignPost(){
        const selectedExams = document.querySelectorAll('input[name="selected-exams[]"]:checked')
        const selectedStudents = document.querySelectorAll('input[name="student-id[]"]:checked')
        const completeByDate = document.querySelector('input[name="exam-due-date"]').value
        const completeByTime = document.querySelector('input[name="exam-due-time"]').value

        const examIds = []
        const stuIds = []
        selectedExams.forEach((exam) => {
            examIds.push(exam.value)
        })

        selectedStudents.forEach((stu) => {
            stuIds.push(stu.value)
        })

        //create assigned_exam post 
        fetch(simulatorData.root_url + '/wp-json/custom/v1/create-assign-post', {
            method : 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                examIds, stuIds, instId: this.instId, completeByDate, completeByTime
            })
        })
        .then(() => {
            location.reload()
        })
        .catch(error => {
            console.log('error', error)
        })
        
    }

    
}

export default InstructorDashboard