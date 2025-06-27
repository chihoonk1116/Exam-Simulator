// get due value, improve response

class Dashboard {
  constructor() {

    this.studentButtons = document.querySelectorAll('.select-student-btn')
    this.examButtons = document.querySelectorAll('.select-exam-btn')

    this.studentIds = new Map()
    this.examIds = new Map()
    this.instId = document.querySelector('.dashboard-inst').dataset.userid

    this.openOverlayBtn = document.getElementById("manage-exam-btn")
    this.closeOverlayBtn = document.getElementById("close-overlay-btn")
    this.overlayEl = document.querySelector('.manage-exam-overlay')

    this.createExamBtn = document.getElementById('create-new-exam_btn')
    this.closeCreateExamWindow = document.getElementById('close-exam-window_btn')
    this.saveNewExam = document.getElementById('save-new-exam_btn')
    this.createExamWindow = document.querySelector('.create-exam-window')

    this.assignBtn = document.getElementById('assign-exam_btn')
    this.unassignBtn = document.getElementById('unassign-exam_btn')
    this.editDueBtn = document.getElementById('edit-due_btn')

    this.dueDateInput = document.getElementById('due-date')
    this.isNoDueDate = document.getElementById('no-due-date')

    

    this.events()
  }

  events(){

    this.studentButtons.forEach(btn => 
      btn.addEventListener('click', this.selectStudentHandler.bind(this))     
    );

    this.examButtons.forEach(btn => 
      btn.addEventListener('click', this.selectExamHandler.bind(this))     
    );

    this.openOverlayBtn.addEventListener('click', () => 
      this.openOverlayHandler()
    )

    this.closeOverlayBtn.addEventListener('click', () =>{
      this.overlayEl.classList.add('hidden')
    })

    this.assignBtn.addEventListener('click', this.createAssignPost.bind(this))
    this.unassignBtn.addEventListener('click', this.deleteAssignPost.bind(this))
    this.editDueBtn.addEventListener('click', this.editDueDate.bind(this))

    this.dueDateInput.addEventListener('input', () => {
      this.isNoDueDate.checked = false
    })

    this.isNoDueDate.addEventListener('input', () => {
      this.dueDateInput.value = ''
    })

    this.createExamBtn.addEventListener('click', () => {
      this.createExamWindow.classList.remove('hidden')
    })

    this.closeCreateExamWindow.addEventListener('click', () => {
      this.createExamWindow.classList.add('hidden')
    })

    this.saveNewExam.addEventListener('click', () => {
      this.createNewExam()
    })
  }

  selectStudentHandler(event){

    const btn = event.currentTarget
    const studentId = btn.dataset.studentId
    const studentName = btn.dataset.studentName

    if(btn.classList.contains("selected")){
      this.studentIds.delete(studentId)
      btn.classList.remove("selected")
    }
    else{
      this.studentIds.set(studentId, studentName)
      btn.classList.add("selected")
    }
      
  }

  selectExamHandler(event){

    const btn = event.currentTarget
    const examId = btn.dataset.examId
    const examTitle = btn.dataset.examTitle

    if(btn.classList.contains("selected")){
      this.examIds.delete(examId)
      btn.classList.remove("selected")
    }
    else{
      this.examIds.set(examId, examTitle)
      btn.classList.add("selected")
    }

  }

  openOverlayHandler(){

    const overlaySelectedExamsEl = document.querySelector(".manage-exam-overlay_selected-exams")
    const overlaySelectedStudentsEl = document.querySelector(".manage-exam-overlay_selected-students")
    
    overlaySelectedExamsEl.innerHTML = this.examIds.size === 0 ? 'Please Select Exams' : '';
    overlaySelectedStudentsEl.innerHTML = this.studentIds.size === 0 ? 'Please Select Students' : '';

    this.overlayEl.classList.remove('hidden')

    this.studentIds.forEach((name) => {
      const span = document.createElement('span')
      span.classList.add('selected-items')
      span.textContent = name
      overlaySelectedStudentsEl.appendChild(span)
    })

    this.examIds.forEach((title) => {
      const span = document.createElement('span')
      span.classList.add('selected-items')
      span.textContent = title
      overlaySelectedExamsEl.appendChild(span)
    })
  }

  createAssignPost(){

    if(this.studentIds.size === 0 || this.examIds.size === 0){
      console.log("Please select student or exam")
      return
    }
    
    let completeByDate = this.dueDateInput.value
    const isNoDue = this.isNoDueDate.checked

    if(isNoDue){
      completeByDate = '9999-12-31'
    }

    //create assigned_exam post 
    fetch(simulatorData.root_url + '/wp-json/custom/v1/create-assign-post', {
      method : 'POST',
      headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce' : simulatorData.api_nonce
      },
      body: JSON.stringify({
          examIds: Array.from(this.examIds.keys()), 
          stuIds: Array.from(this.studentIds.keys()), 
          instId: this.instId, 
          completeByDate, 
          completeByTime: ""
      })
    })
    .then((res) => {
      // add spinner and alert, improve ux
      location.reload();
    })
    .catch(error => {
      console.log('error', error)
    })
  }

  deleteAssignPost(){

    if(this.studentIds.size === 0 || this.examIds.size === 0){
      console.log("Please select student or exam")
      return
    }

    fetch(simulatorData.root_url + '/wp-json/custom/v1/delete-assign-post', {
      method: 'POST',
      headers: {
        'Content-Type' : 'application/json',
        'X-WP-Nonce': simulatorData.api_nonce
      },
      body: JSON.stringify({
        examIds: Array.from(this.examIds.keys()),
        studentIds: Array.from(this.studentIds.keys())
      })
    })
    .then((res) => {
      location.reload();
    })
    .catch((err) => {
      console.log(err)
    })
  }

  editDueDate(){

    if(this.studentIds.size === 0 || this.examIds.size === 0){
      console.log("Please select student or exam")
      return
    }

    let completeByDate = this.dueDateInput.value
    const isNoDue = this.isNoDueDate.checked

    if(isNoDue){
      completeByDate = '9999-12-31'
    }

    fetch(simulatorData.root_url + '/wp-json/custom/v1/edit-due-date', {
      method: 'POST',
      headers: {
        'Content-Type' : 'application/json',
        'X-WP-Nonce': simulatorData.api_nonce
      },
      body: JSON.stringify({
        examIds: Array.from(this.examIds.keys()),
        studentIds: Array.from(this.studentIds.keys()),
        completeByDate
      })
    })
    .then((res) => {
      console.log(res)
    })
    .catch((err) => {
      console.log(err)
    })
  }

  createNewExam(){
    
    const examTitle = document.getElementById('exam-title').value
    
    fetch(simulatorData.root_url + '/wp-json/wp/v2/exam', {
      method: 'POST',
      headers: {
        'Content-Type' : 'application/json',
        'X-WP-Nonce' : simulatorData.api_nonce
      },
      body: JSON.stringify({
        title: examTitle,
        status: 'publish'
      })
    })
    .then((res) => {
      console.log(res)
    })
    .catch((err) => {
      console.log(err)
    })
    
  }
}

export default Dashboard