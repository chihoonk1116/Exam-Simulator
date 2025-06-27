/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./src/modules/AssignedExam.js":
/*!*************************************!*\
  !*** ./src/modules/AssignedExam.js ***!
  \*************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
class AssignedExam {
  constructor() {
    this.examContainer = document.querySelector('.assigned-exam-container');
    if (this.examContainer) {
      this.examStatus = this.examContainer.dataset.status;
      this.examDuration = this.examContainer.dataset.duration.split(':');
    }
    if (this.examStatus === 'assigned') {
      this.allQuizBlocks = document.querySelectorAll('.quiz-answers');
      this.displayElement = document.getElementById('answered-count');
      this.answeredQuestions = new Set();
      this.answerCount = 0;
      this.displayElement.textContent = `0 / ${this.allQuizBlocks.length}`;
      if (this.examDuration) {
        this.hours = parseInt(this.examDuration[0]);
        this.minutes = parseInt(this.examDuration[1]);
      }
      this.timer = null;
      this.timeDisplay = document.getElementById('timer');
      this.examSubmitBtn = document.getElementById('exam-submit');
      this.initTimer();
      this.events();
    }
  }
  events() {
    this.allQuizBlocks.forEach(block => {
      const inputs = block.querySelectorAll('input[type="checkbox"], input[type="radio"]');
      if (inputs.length === 0) return;
      const nameAttr = inputs[0].getAttribute('name');
      const questionId = nameAttr.replace(/\[\]$/, '');
      inputs.forEach(input => {
        input.addEventListener('change', () => this.updateAnswerStatus(block, questionId));
      });
    });
  }
  updateAnswerStatus(block, questionId) {
    const checkedInputs = block.querySelectorAll('input:checked');
    const isAnswers = checkedInputs.length > 0;
    if (isAnswers > 0 && !this.answeredQuestions.has(questionId)) {
      this.answeredQuestions.add(questionId);
      this.answerCount++;
    } else if (!isAnswers && this.answeredQuestions.has(questionId)) {
      this.answeredQuestions.delete(questionId);
      this.answerCount--;
    }
    this.displayElement.textContent = `${this.answerCount} / ${this.allQuizBlocks.length}`;
  }
  initTimer() {
    if (this.hours === 0 && this.minutes === 0) {
      this.timeDisplay.textContent = 'Click submit button when you finish the exam';
    } else {
      this.timeDisplay.textContent = this.hours + 'h ' + this.minutes + ' min left';
      this.timer = setInterval(this.updateTimer.bind(this), 100);
    }
  }
  updateTimer() {
    this.minutes--;
    if (this.hours === 0 && this.minutes === 0) {
      this.examSubmitBtn.click();
      clearInterval(this.timer);
    } else if (this.minutes === 0) {
      this.minutes = 59;
      this.hours--;
    }
    const formattedMinutes = String(this.minutes).padStart(2, '0');
    const formattedHours = String(this.hours).padStart(2, '0');
    this.timeDisplay.textContent = `${formattedHours} h ${formattedMinutes}min left`;
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (AssignedExam);

/***/ }),

/***/ "./src/modules/Dashboard.js":
/*!**********************************!*\
  !*** ./src/modules/Dashboard.js ***!
  \**********************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
// get due value, improve response

class Dashboard {
  constructor() {
    this.studentButtons = document.querySelectorAll('.select-student-btn');
    this.examButtons = document.querySelectorAll('.select-exam-btn');
    this.studentIds = new Map();
    this.examIds = new Map();
    this.instId = document.querySelector('.dashboard-inst').dataset.userid;
    this.openOverlayBtn = document.getElementById("manage-exam-btn");
    this.closeOverlayBtn = document.getElementById("close-overlay-btn");
    this.overlayEl = document.querySelector('.manage-exam-overlay');
    this.createExamBtn = document.getElementById('create-new-exam_btn');
    this.closeCreateExamWindow = document.getElementById('close-exam-window_btn');
    this.saveNewExam = document.getElementById('save-new-exam_btn');
    this.createExamWindow = document.querySelector('.create-exam-window');
    this.assignBtn = document.getElementById('assign-exam_btn');
    this.unassignBtn = document.getElementById('unassign-exam_btn');
    this.editDueBtn = document.getElementById('edit-due_btn');
    this.dueDateInput = document.getElementById('due-date');
    this.isNoDueDate = document.getElementById('no-due-date');
    this.events();
  }
  events() {
    this.studentButtons.forEach(btn => btn.addEventListener('click', this.selectStudentHandler.bind(this)));
    this.examButtons.forEach(btn => btn.addEventListener('click', this.selectExamHandler.bind(this)));
    this.openOverlayBtn.addEventListener('click', () => this.openOverlayHandler());
    this.closeOverlayBtn.addEventListener('click', () => {
      this.overlayEl.classList.add('hidden');
    });
    this.assignBtn.addEventListener('click', this.createAssignPost.bind(this));
    this.unassignBtn.addEventListener('click', this.deleteAssignPost.bind(this));
    this.editDueBtn.addEventListener('click', this.editDueDate.bind(this));
    this.dueDateInput.addEventListener('input', () => {
      this.isNoDueDate.checked = false;
    });
    this.isNoDueDate.addEventListener('input', () => {
      this.dueDateInput.value = '';
    });
    this.createExamBtn.addEventListener('click', () => {
      this.createExamWindow.classList.remove('hidden');
    });
    this.closeCreateExamWindow.addEventListener('click', () => {
      this.createExamWindow.classList.add('hidden');
    });
    this.saveNewExam.addEventListener('click', () => {
      this.createNewExam();
    });
  }
  selectStudentHandler(event) {
    const btn = event.currentTarget;
    const studentId = btn.dataset.studentId;
    const studentName = btn.dataset.studentName;
    if (btn.classList.contains("selected")) {
      this.studentIds.delete(studentId);
      btn.classList.remove("selected");
    } else {
      this.studentIds.set(studentId, studentName);
      btn.classList.add("selected");
    }
  }
  selectExamHandler(event) {
    const btn = event.currentTarget;
    const examId = btn.dataset.examId;
    const examTitle = btn.dataset.examTitle;
    if (btn.classList.contains("selected")) {
      this.examIds.delete(examId);
      btn.classList.remove("selected");
    } else {
      this.examIds.set(examId, examTitle);
      btn.classList.add("selected");
    }
  }
  openOverlayHandler() {
    const overlaySelectedExamsEl = document.querySelector(".manage-exam-overlay_selected-exams");
    const overlaySelectedStudentsEl = document.querySelector(".manage-exam-overlay_selected-students");
    overlaySelectedExamsEl.innerHTML = this.examIds.size === 0 ? 'Please Select Exams' : '';
    overlaySelectedStudentsEl.innerHTML = this.studentIds.size === 0 ? 'Please Select Students' : '';
    this.overlayEl.classList.remove('hidden');
    this.studentIds.forEach(name => {
      const span = document.createElement('span');
      span.classList.add('selected-items');
      span.textContent = name;
      overlaySelectedStudentsEl.appendChild(span);
    });
    this.examIds.forEach(title => {
      const span = document.createElement('span');
      span.classList.add('selected-items');
      span.textContent = title;
      overlaySelectedExamsEl.appendChild(span);
    });
  }
  createAssignPost() {
    if (this.studentIds.size === 0 || this.examIds.size === 0) {
      console.log("Please select student or exam");
      return;
    }
    let completeByDate = this.dueDateInput.value;
    const isNoDue = this.isNoDueDate.checked;
    if (isNoDue) {
      completeByDate = '9999-12-31';
    }

    //create assigned_exam post 
    fetch(simulatorData.root_url + '/wp-json/custom/v1/create-assign-post', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': simulatorData.api_nonce
      },
      body: JSON.stringify({
        examIds: Array.from(this.examIds.keys()),
        stuIds: Array.from(this.studentIds.keys()),
        instId: this.instId,
        completeByDate,
        completeByTime: ""
      })
    }).then(res => {
      // add spinner and alert, improve ux
      location.reload();
    }).catch(error => {
      console.log('error', error);
    });
  }
  deleteAssignPost() {
    if (this.studentIds.size === 0 || this.examIds.size === 0) {
      console.log("Please select student or exam");
      return;
    }
    fetch(simulatorData.root_url + '/wp-json/custom/v1/delete-assign-post', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': simulatorData.api_nonce
      },
      body: JSON.stringify({
        examIds: Array.from(this.examIds.keys()),
        studentIds: Array.from(this.studentIds.keys())
      })
    }).then(res => {
      location.reload();
    }).catch(err => {
      console.log(err);
    });
  }
  editDueDate() {
    if (this.studentIds.size === 0 || this.examIds.size === 0) {
      console.log("Please select student or exam");
      return;
    }
    let completeByDate = this.dueDateInput.value;
    const isNoDue = this.isNoDueDate.checked;
    if (isNoDue) {
      completeByDate = '9999-12-31';
    }
    fetch(simulatorData.root_url + '/wp-json/custom/v1/edit-due-date', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': simulatorData.api_nonce
      },
      body: JSON.stringify({
        examIds: Array.from(this.examIds.keys()),
        studentIds: Array.from(this.studentIds.keys()),
        completeByDate
      })
    }).then(res => {
      console.log(res);
    }).catch(err => {
      console.log(err);
    });
  }
  createNewExam() {
    const examTitle = document.getElementById('exam-title').value;
    fetch(simulatorData.root_url + '/wp-json/wp/v2/exam', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-WP-Nonce': simulatorData.api_nonce
      },
      body: JSON.stringify({
        title: examTitle,
        status: 'publish'
      })
    }).then(res => {
      console.log(res);
    }).catch(err => {
      console.log(err);
    });
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (Dashboard);

/***/ }),

/***/ "./src/modules/InstructorDashboard.js":
/*!********************************************!*\
  !*** ./src/modules/InstructorDashboard.js ***!
  \********************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
class InstructorDashboard {
  constructor() {
    this.dashboardSection = document.querySelector(".dashboard-section");
    if (this.dashboardSection) {
      this.instId = this.dashboardSection.dataset.userid;
      this.addExambtn = document.querySelector(".add-exam");
      this.createForm = document.querySelector(".create-exam-form");
      this.assignMultipleExamsBtn = document.getElementById("assign_multiple_exams");
      this.events();
    }
  }
  events() {
    if (this.addExambtn) {
      this.addExambtn.addEventListener('click', () => {
        this.createForm.classList.toggle('hidden');
      });
    }
    if (this.assignMultipleExamsBtn) {
      this.assignMultipleExamsBtn.addEventListener('click', () => this.createAssignPost());
    }
  }
  createAssignPost() {
    const selectedExams = document.querySelectorAll('input[name="selected-exams[]"]:checked');
    const selectedStudents = document.querySelectorAll('input[name="student-id[]"]:checked');
    const completeByDate = document.querySelector('input[name="exam-due-date"]').value;
    const completeByTime = document.querySelector('input[name="exam-due-time"]').value;
    const examIds = [];
    const stuIds = [];
    selectedExams.forEach(exam => {
      examIds.push(exam.value);
    });
    selectedStudents.forEach(stu => {
      stuIds.push(stu.value);
    });

    //create assigned_exam post 
    fetch(simulatorData.root_url + '/wp-json/custom/v1/create-assign-post', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        examIds,
        stuIds,
        instId: this.instId,
        completeByDate,
        completeByTime
      })
    }).then(() => {
      location.reload();
    }).catch(error => {
      console.log('error', error);
    });
  }
}
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (InstructorDashboard);

/***/ })

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!**********************!*\
  !*** ./src/index.js ***!
  \**********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _modules_AssignedExam__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./modules/AssignedExam */ "./src/modules/AssignedExam.js");
/* harmony import */ var _modules_InstructorDashboard__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./modules/InstructorDashboard */ "./src/modules/InstructorDashboard.js");
/* harmony import */ var _modules_Dashboard__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./modules/Dashboard */ "./src/modules/Dashboard.js");



const assignedExam = new _modules_AssignedExam__WEBPACK_IMPORTED_MODULE_0__["default"]();
const instructorDashboard = new _modules_InstructorDashboard__WEBPACK_IMPORTED_MODULE_1__["default"]();
const dashboard = new _modules_Dashboard__WEBPACK_IMPORTED_MODULE_2__["default"]();
})();

/******/ })()
;
//# sourceMappingURL=index.js.map