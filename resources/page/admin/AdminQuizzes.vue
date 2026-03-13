<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Quiz Builder</p>
        <h1>Конструктор олимпиад</h1>
        <p class="subtext">Создавайте 25 вопросов с вариантами A-E, редактируйте и публикуйте олимпиады.</p>
      </div>
      <button class="primary-btn" @click="openCreateModal">Новая олимпиада</button>
    </header>

    <div v-if="loading" class="loading-card">Загружаем олимпиады...</div>

    <div v-else-if="!quizzes.length" class="empty-card">
      <h2>Олимпиад пока нет</h2>
      <p>Создайте первую олимпиаду через ручной конструктор.</p>
    </div>

    <div v-else class="quiz-grid">
      <article v-for="quiz in quizzes" :key="quiz.id" class="quiz-card">
        <div class="quiz-head">
          <div>
            <span class="subject-chip">{{ quiz.subject?.name || 'Без предмета' }}</span>
            <h2>{{ quiz.title }}</h2>
          </div>
          <span class="status-chip" :class="quiz.is_published ? 'published' : 'draft'">
            {{ quiz.is_published ? 'Опубликовано' : 'Черновик' }}
          </span>
        </div>

        <p class="quiz-desc">{{ quiz.description || 'Без описания' }}</p>

        <div class="quiz-meta">
          <span>{{ quiz.questions_count || 0 }} вопросов</span>
          <span>{{ quiz.time_limit }} мин</span>
        </div>

        <div class="quiz-actions">
          <button class="ghost-btn" @click="editQuiz(quiz)">Редактировать</button>
          <button class="ghost-btn" @click="togglePublish(quiz)">
            {{ quiz.is_published ? 'Снять с публикации' : 'Опубликовать' }}
          </button>
          <button class="danger-btn" @click="deleteQuiz(quiz.id)">Удалить</button>
        </div>
      </article>
    </div>

    <div v-if="showModal" class="modal-backdrop" @click.self="closeModal">
      <div class="modal-card">
        <div class="modal-head">
          <div>
            <p class="eyebrow">{{ editingId ? 'Edit quiz' : 'Create quiz' }}</p>
            <h2>{{ editingId ? 'Редактирование олимпиады' : 'Новая олимпиада' }}</h2>
          </div>
          <button class="icon-btn" @click="closeModal">x</button>
        </div>

        <div class="form-grid">
          <label class="field full">
            <span>Название олимпиады</span>
            <input v-model="form.title" type="text" />
          </label>

          <label class="field full">
            <span>Описание</span>
            <textarea v-model="form.description" rows="3"></textarea>
          </label>

          <label class="field">
            <span>Лимит времени, минут</span>
            <input v-model.number="form.time_limit" type="number" min="1" max="180" />
          </label>

          <label class="field">
            <span>Количество вопросов</span>
            <input
              v-model.number="questionCountInput"
              type="number"
              min="1"
              max="100"
              @change="applyQuestionCount"
            />
          </label>

          <label class="field checkbox-field">
            <input v-model="form.is_published" type="checkbox" />
            <span>Опубликовать сразу</span>
          </label>

          <label class="field">
            <span>Выбрать предмет</span>
            <select v-model="form.subject_id">
              <option :value="null">Создать новый предмет</option>
              <option v-for="subject in subjects" :key="subject.id" :value="subject.id">
                {{ subject.name }}
              </option>
            </select>
          </label>

          <template v-if="!form.subject_id">
            <label class="field">
              <span>Новый предмет</span>
              <input v-model="form.subject.name" type="text" />
            </label>
            <label class="field">
              <span>Дата старта</span>
              <input v-model="form.subject.start_date" type="date" />
            </label>
            <label class="field full">
              <span>Описание предмета</span>
              <textarea v-model="form.subject.description" rows="2"></textarea>
            </label>
            <label class="field full">
              <span>Ссылка на изображение предмета</span>
              <input v-model="form.subject.image" type="text" />
            </label>
          </template>
        </div>

        <div class="questions-header">
          <h3>Вопросы</h3>
          <span>Выберите нужное количество вопросов. В каждом вопросе остаются 5 вариантов A-E.</span>
        </div>

        <div class="questions-list">
          <section
            v-for="(question, qIndex) in form.questions"
            :key="qIndex"
            class="question-card"
          >
            <div class="question-title">Вопрос {{ qIndex + 1 }}</div>
            <label class="field full">
              <span>Текст вопроса</span>
              <textarea v-model="question.question" rows="2"></textarea>
            </label>

            <div class="answers-grid">
              <label
                v-for="(answer, aIndex) in question.answers"
                :key="answer.label"
                class="field"
              >
                <span>Вариант {{ answer.label }}</span>
                <input v-model="answer.answer" type="text" />
                <label class="radio-row">
                  <input
                    v-model="question.correct_answer"
                    type="radio"
                    :name="`correct-${qIndex}`"
                    :value="answer.label"
                  />
                  <span>Правильный ответ</span>
                </label>
              </label>
            </div>
          </section>
        </div>

        <p v-if="formError" class="error-text">{{ formError }}</p>

        <div class="modal-actions">
          <button class="ghost-btn" @click="closeModal">Отмена</button>
          <button class="primary-btn" :disabled="saving" @click="saveQuiz">
            {{ saving ? 'Сохранение...' : 'Сохранить олимпиаду' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref } from 'vue'
import api from '../../js/api'

const quizzes = ref([])
const subjects = ref([])
const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const formError = ref('')
const questionCountInput = ref(25)

const createEmptyQuestion = (index) => ({
  question: '',
  position: index + 1,
  correct_answer: 'A',
  answers: ['A', 'B', 'C', 'D', 'E'].map((label, answerIndex) => ({
    label,
    position: answerIndex + 1,
    answer: '',
  })),
})

const createForm = () => ({
  subject_id: null,
  subject: {
    name: '',
    description: '',
    image: '',
    start_date: '',
  },
  title: '',
  description: '',
  time_limit: 60,
  is_published: false,
  questions: Array.from({ length: 25 }, (_, index) => createEmptyQuestion(index)),
})

const form = ref(createForm())

const loadData = async () => {
  loading.value = true
  try {
    const [quizRes, subjectRes] = await Promise.all([
      api.get('/admin/quizzes'),
      api.get('/subjects'),
    ])

    quizzes.value = quizRes.data
    subjects.value = subjectRes.data
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
  editingId.value = null
  formError.value = ''
  form.value = createForm()
  questionCountInput.value = form.value.questions.length
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const editQuiz = async (quiz) => {
  formError.value = ''
  editingId.value = quiz.id
  const { data } = await api.get(`/admin/quizzes/${quiz.id}`)

  form.value = {
    subject_id: data.subject?.id ?? null,
    subject: {
      name: '',
      description: '',
      image: '',
      start_date: '',
    },
    title: data.title,
    description: data.description ?? '',
    time_limit: data.time_limit,
    is_published: data.is_published,
    questions: data.questions.map((question, questionIndex) => ({
      question: question.question,
      position: question.position ?? questionIndex + 1,
      correct_answer:
        question.answers.find((answer, answerIndex) => answer.is_correct)?.label ||
        ['A', 'B', 'C', 'D', 'E'][question.answers.findIndex((answer) => answer.is_correct)] ||
        'A',
      answers: question.answers.map((answer, answerIndex) => ({
        label: answer.label || ['A', 'B', 'C', 'D', 'E'][answerIndex],
        position: answer.position ?? answerIndex + 1,
        answer: answer.answer,
      })),
    })),
  }

  questionCountInput.value = form.value.questions.length

  showModal.value = true
}

const applyQuestionCount = () => {
  const targetCount = Math.min(100, Math.max(1, Number(questionCountInput.value) || 1))
  questionCountInput.value = targetCount

  const nextQuestions = Array.from({ length: targetCount }, (_, index) => {
    const existing = form.value.questions[index]
    if (!existing) {
      return createEmptyQuestion(index)
    }

    return {
      ...existing,
      position: index + 1,
      answers: existing.answers.map((answer, answerIndex) => ({
        ...answer,
        label: answer.label || ['A', 'B', 'C', 'D', 'E'][answerIndex],
        position: answerIndex + 1,
      })),
    }
  })

  form.value.questions = nextQuestions
}

const validateForm = () => {
  if (!form.value.title.trim()) return 'Введите название олимпиады.'
  if (!form.value.subject_id && !form.value.subject.name.trim()) return 'Введите название предмета.'
  if (!form.value.questions.length) return 'Добавьте хотя бы один вопрос.'

  for (const [index, question] of form.value.questions.entries()) {
    if (!question.question.trim()) return `Заполните текст вопроса ${index + 1}.`
    if (question.answers.length !== 5) return `В вопросе ${index + 1} должно быть 5 вариантов.`

    for (const answer of question.answers) {
      if (!answer.answer.trim()) {
        return `Заполните вариант ${answer.label} в вопросе ${index + 1}.`
      }
    }
  }

  return ''
}

const normalizePayload = () => ({
  subject_id: form.value.subject_id,
  subject: form.value.subject_id
    ? undefined
    : {
        ...form.value.subject,
        start_date: form.value.subject.start_date || new Date().toISOString().slice(0, 10),
      },
  title: form.value.title.trim(),
  description: form.value.description.trim(),
  time_limit: Number(form.value.time_limit) || 60,
  is_published: !!form.value.is_published,
  questions: form.value.questions.map((question, questionIndex) => ({
    question: question.question.trim(),
    position: questionIndex + 1,
    correct_answer: question.correct_answer,
    answers: question.answers.map((answer, answerIndex) => ({
      label: answer.label,
      position: answerIndex + 1,
      answer: answer.answer.trim(),
    })),
  })),
})

const saveQuiz = async () => {
  formError.value = validateForm()
  if (formError.value) return

  saving.value = true
  try {
    const payload = normalizePayload()
    if (editingId.value) {
      await api.put(`/admin/quizzes/${editingId.value}`, payload)
    } else {
      await api.post('/admin/quizzes', payload)
    }

    await loadData()
    closeModal()
  } catch (error) {
    formError.value = error.response?.data?.message || 'Не удалось сохранить олимпиаду.'
  } finally {
    saving.value = false
  }
}

const togglePublish = async (quiz) => {
  const endpoint = quiz.is_published
    ? `/admin/quizzes/${quiz.id}/unpublish`
    : `/admin/quizzes/${quiz.id}/publish`

  await api.post(endpoint)
  await loadData()
}

const deleteQuiz = async (id) => {
  if (!window.confirm('Удалить олимпиаду?')) return
  await api.delete(`/admin/quizzes/${id}`)
  await loadData()
}

onMounted(loadData)
</script>

<style scoped>
* { box-sizing: border-box; }

.admin-page {
  min-height: 100vh;
  background:
    radial-gradient(circle at top right, rgba(37, 99, 235, 0.12), transparent 24%),
    linear-gradient(180deg, #f4f7fb 0%, #e9eef7 100%);
  padding: 28px;
  color: #102347;
}

.header {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.eyebrow {
  margin: 0 0 8px;
  text-transform: uppercase;
  letter-spacing: 0.08em;
  color: #2563eb;
  font-size: 12px;
  font-weight: 700;
}

h1,
h2,
h3 {
  margin: 0;
  color: #102347;
}

.subtext {
  margin: 10px 0 0;
  color: #55708f;
}

.primary-btn,
.ghost-btn,
.danger-btn {
  border: 0;
  border-radius: 16px;
  padding: 13px 18px;
  font-weight: 800;
  cursor: pointer;
}

.primary-btn {
  background: linear-gradient(135deg, #2563eb, #1d4ed8);
  color: #fff;
  box-shadow: 0 14px 28px rgba(37, 99, 235, 0.22);
}

.ghost-btn {
  background: #edf4ff;
  color: #173a72;
}

.danger-btn {
  background: #ffe4e6;
  color: #be123c;
}

.loading-card,
.empty-card,
.quiz-card,
.modal-card {
  background: rgba(255, 255, 255, 0.94);
  border: 1px solid rgba(148, 163, 184, 0.2);
  border-radius: 26px;
  box-shadow: 0 18px 48px rgba(15, 35, 85, 0.08);
}

.loading-card,
.empty-card {
  padding: 28px;
}

.quiz-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 16px;
}

.quiz-card {
  padding: 22px;
}

.quiz-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.subject-chip,
.status-chip {
  display: inline-flex;
  padding: 6px 10px;
  border-radius: 999px;
  font-size: 12px;
  font-weight: 700;
}

.subject-chip { background: #edf4ff; color: #2563eb; margin-bottom: 10px; }
.status-chip.published { background: #e8f8ed; color: #1f7a34; }
.status-chip.draft { background: #fff7db; color: #9c6b05; }

.quiz-desc {
  color: #5b6f89;
  line-height: 1.5;
}

.quiz-meta,
.quiz-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 16px;
}

.quiz-meta span {
  color: #55708f;
  font-size: 14px;
}

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(15, 23, 42, 0.48);
  padding: 20px;
  overflow-y: auto;
}

.modal-card {
  width: min(1200px, 100%);
  margin: 0 auto;
  padding: 28px;
}

.modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 16px;
  margin-bottom: 20px;
}

.icon-btn {
  width: 40px;
  height: 40px;
  border: 0;
  border-radius: 14px;
  background: #edf4ff;
  color: #102347;
  font-size: 22px;
  font-weight: 800;
  cursor: pointer;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 14px;
}

.field {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.full { grid-column: 1 / -1; }

.field span {
  color: #36506c;
  font-size: 13px;
  font-weight: 700;
}

input,
textarea,
select {
  width: 100%;
  border: 1px solid #cad8ea;
  border-radius: 16px;
  padding: 13px 15px;
  font: inherit;
  background: #fbfdff;
  color: #102347;
  outline: none;
}

input::placeholder,
textarea::placeholder {
  color: #92a3ba;
}

input:focus,
textarea:focus,
select:focus {
  border-color: #2563eb;
  box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
}

.checkbox-field {
  flex-direction: row;
  align-items: center;
  gap: 10px;
  padding-top: 32px;
}

.checkbox-field input {
  width: auto;
  accent-color: #2563eb;
}

.questions-header {
  margin: 24px 0 14px;
  display: flex;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px;
}

.questions-header span {
  color: #55708f;
  font-size: 14px;
}

.questions-list {
  display: grid;
  gap: 14px;
}

.question-card {
  border: 1px solid #dbe7f7;
  border-radius: 20px;
  padding: 18px;
  background: linear-gradient(180deg, #f8fbff 0%, #f3f8ff 100%);
}

.question-title {
  font-weight: 700;
  color: #102347;
  margin-bottom: 12px;
}

.answers-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.radio-row {
  display: flex;
  align-items: center;
  gap: 8px;
  color: #55708f;
  font-size: 13px;
}

.radio-row input {
  width: auto;
  accent-color: #2563eb;
}

.error-text {
  margin: 16px 0 0;
  color: #b91c1c;
  font-weight: 600;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}

@media (max-width: 900px) {
  .form-grid,
  .answers-grid {
    grid-template-columns: 1fr;
  }
}

@media (max-width: 640px) {
  .admin-page,
  .modal-backdrop {
    padding: 16px;
  }

  .header,
  .modal-head,
  .questions-header,
  .modal-actions {
    flex-direction: column;
  }

  .modal-actions .primary-btn,
  .modal-actions .ghost-btn,
  .header .primary-btn {
    width: 100%;
  }
}
</style>
