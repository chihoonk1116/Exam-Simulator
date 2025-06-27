import './edit.css'
import {subscribe, useSelect} from '@wordpress/data'
import { useBlockProps } from '@wordpress/block-editor';
import apiFetch from "@wordpress/api-fetch";
import { useEffect, useState } from 'react';

wp.blocks.registerBlockType("custom/question-block", {
    edit: EditComponent,
    save: function(){
        return null
    }
});

function EditComponent(props){
    const {attributes, setAttributes, clientId} = props
    const {questionId} = attributes
    const { select } = wp.data;
    const [questionsInTheExam, setQuestionsInTheExam] = useState(undefined)
    const [previewHTML, setPreviewHTML] = useState()

    useEffect(()=>{
        async function fetchPreviewHTML(){
            const res = await apiFetch({
                path: `/questionBlock/v1/getHTML?questionId=${questionId}`,
                method: 'GET'
            })
            setPreviewHTML(res)
        }
        fetchPreviewHTML()
    }, [questionId])

    useEffect(() => {
        if(questionsInTheExam === undefined){
            const examPostID = select('core/editor').getCurrentPost().id
            async function getQuestionsID(){
                const res = await apiFetch({
                    path: `/simulator/v1/question?examId=${examPostID}`,
                    method: "GET"
                })
                setQuestionsInTheExam(res['questions'])
            }
            getQuestionsID()
        }

        let timeoutId
        const subsBlockIds = subscribe(() => {
            clearTimeout(timeoutId)

            timeoutId = setTimeout(() => {
                const blocks = select('core/block-editor').getBlocks()
                const ids = blocks
                    .filter((block) => block.name === 'custom/question-block')
                    .map((block) => block.attributes.questionId)

                setQuestionsInTheExam(ids)
            }, 300)
        })
        return () => {
            clearTimeout(timeoutId)
            subsBlockIds()
        }
    }, [])

    const allQuestions = useSelect((select) => {
        return select('core').getEntityRecords('postType', "question");
    })

    // const allQuestions = select('core').getEntityRecords('postType', "question");
    
    const blockInd = useSelect((select) => {
        const {getBlockIndex} = select('core/block-editor')
        const rootClientId = select('core/block-editor').getBlockRootClientId(clientId)
        const index = getBlockIndex(clientId, rootClientId)
        return index;
    }, [clientId])

    // const rootClientId = select('core/block-editor').getBlockRootClientId(clientId)
    // const blockInd = select('core/block-editor').getBlockIndex(clientId, rootClientId)
    
    // const post = select("core").getEntityRecord("postType", "question", questionId)
    // const post = useSelect(select => {
    //         return select("core").getEntityRecord("postType", "question", questionId)
    // }, [questionId])

    function selectQuiz(){
       return (
        <div> 
        {(!allQuestions || !questionsInTheExam) ?
            <p>Loading...</p>
            :
            <select name="" id="" onChange={e => setAttributes({questionId: parseInt(e.target.value, 10)})}>
                <option value="">Select a question...</option>
                {allQuestions.map(q => {
                    return (
                        <option 
                            value={q.id} 
                            selected={questionId == q.id}  
                            disabled={questionsInTheExam.includes(q.id)}
                        >
                            {q.title.rendered}
                        </option>
                    )
                })}
            </select>
        }
        </div>
       )
    
    }

    // function renderQuizBlock(){
    //     return (
    //         <div className="quiz-block">
    //            {selectQuiz()}
    //            <div className="quiz-heading">
    //               <h3>Q{blockInd + 1}. {post.title.rendered}</h3>
    //            </div>
    //            <div className="quiz-answers">
    //               <h4>Correct Answer : {post.correct_answer}</h4>
    //               <ol>
    //                 {post.answers.map(answer => {
    //                     return <li>{answer}</li>
    //                 })}
    //               </ol>
    //            </div>
    //         </div>
    //     )
    // }
    
    return (
        <div {...useBlockProps()}> 
            {(!questionId)? (
                selectQuiz()
            ) : (
                !previewHTML) ? (
                    <p>Loading...</p>
                ) : (
                    <>
                        {selectQuiz()}
                        <div dangerouslySetInnerHTML={{__html: previewHTML}}></div>
                    </>
                    
                )
            }
        </div>
    )
}