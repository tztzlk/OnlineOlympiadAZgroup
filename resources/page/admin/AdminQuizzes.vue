<template>
  <div class="admin-page">
    <header class="header">
      <div>
        <p class="eyebrow">Quiz Builder</p>
        <h1>Конструктор олимпиад</h1>
        <p class="subtext">
          Одна олимпиада, несколько категорий по классам и отдельные наборы вопросов в каждой категории.
          Для вопроса можно добавить изображение по ссылке или загрузить файл.
        </p>
      </div>
      <button class="primary-btn" @click="openCreateModal">Новая олимпиада</button>
    </header>

    <div v-if="loading" class="loading-card">Загружаем олимпиады...</div>

    <div v-else-if="!quizzes.length" class="empty-card">
      <h2>Олимпиад пока нет</h2>
      <p>Создайте первую олимпиаду и заполните категории по классам.</p>
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

        <div class="category-list">
          <span v-for="category in quiz.categories" :key="category.id" class="category-chip">
            {{ category.label }}
          </span>
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
          <button class="icon-btn" @click="closeModal">×</button>
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

        <div class="categories-toolbar">
          <div>
            <h3>Категории и вопросы</h3>
            <p>У каждой категории свой набор вопросов. Категория назначается автоматически по классу участника.</p>
          </div>
          <div class="category-tabs">
            <button
              v-for="(category, index) in form.categories"
              :key="category.label"
              type="button"
              class="tab-btn"
              :class="{ active: activeCategoryIndex === index }"
              @click="activeCategoryIndex = index"
            >
              {{ category.label }}
            </button>
          </div>
        </div>

        <section v-if="activeCategory" class="category-editor">
          <div class="category-top">
            <div class="category-heading">
              <h4>{{ activeCategory.label }}</h4>
              <span>
                {{
                  activeCategory.grade_from === activeCategory.grade_to
                    ? `${activeCategory.grade_from} класс`
                    : `${activeCategory.grade_from}-${activeCategory.grade_to} классы`
                }}
              </span>
            </div>

            <label class="mini-field">
              <span>Количество вопросов</span>
              <input
                v-model.number="questionCountInputs[activeCategoryIndex]"
                type="number"
                min="1"
                max="100"
                @change="applyQuestionCount(activeCategoryIndex)"
              />
            </label>
          </div>

          <div class="questions-list">
            <section
              v-for="(question, qIndex) in activeCategory.questions"
              :key="`${activeCategory.label}-${qIndex}`"
              class="question-card"
            >
              <div class="question-head">
                <div class="question-title">Вопрос {{ qIndex + 1 }}</div>
                <span v-if="question.image" class="image-badge">С картинкой</span>
              </div>

              <label class="field full">
                <span>Текст вопроса</span>
                <textarea v-model="question.question" rows="2"></textarea>
              </label>

              <div class="image-tools">
                <label class="field">
                  <span>Источник изображения</span>
                  <select v-model="question.image_source" @change="handleImageSourceChange(question)">
                    <option value="">Без изображения</option>
                    <option value="url">Ссылка</option>
                    <option value="upload">Загрузка файла</option>
                  </select>
                </label>

                <label v-if="question.image_source === 'url'" class="field full">
                  <span>Ссылка на изображение</span>
                  <input
                    v-model="question.image_url"
                    type="url"
                    placeholder="https://example.com/question-image.jpg"
                  />
                </label>

                <div v-if="question.image_source === 'upload'" class="field full">
                  <span>Файл изображения</span>
                  <div class="upload-row">
                    <label class="upload-btn">
                      <input type="file" accept="image/*" @change="uploadQuestionImage($event, question)" />
                      <span>{{ question.uploading ? 'Загрузка...' : 'Загрузить изображение' }}</span>
                    </label>
                    <span class="upload-note">
                      {{ question.image_path ? 'Файл загружен' : 'PNG, JPG, WEBP до 5 МБ' }}
                    </span>
                  </div>
                </div>

                <div v-if="question.image" class="image-preview full">
                  <img :src="question.image" :alt="`Изображение вопроса ${qIndex + 1}`" />
                </div>
              </div>

              <div class="answers-grid">
                <label v-for="answer in question.answers" :key="answer.label" class="field">
                  <span>Вариант {{ answer.label }}</span>
                  <input v-model="answer.answer" type="text" />
                  <label class="radio-row">
                    <input
                      v-model="question.correct_answer"
                      type="radio"
                      :name="`${activeCategory.label}-correct-${qIndex}`"
                      :value="answer.label"
                    />
                    <span>Правильный ответ</span>
                  </label>
                </label>
              </div>
            </section>
          </div>
        </section>

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
import { computed, onMounted, ref } from 'vue'
import api from '../../js/api'

const CATEGORY_PRESETS = [
  { label: '3-4', grade_from: 3, grade_to: 4, sort_order: 1 },
  { label: '5-6', grade_from: 5, grade_to: 6, sort_order: 2 },
  { label: '7-8', grade_from: 7, grade_to: 8, sort_order: 3 },
  { label: '9-10', grade_from: 9, grade_to: 10, sort_order: 4 },
  { label: '11', grade_from: 11, grade_to: 11, sort_order: 5 },
]

const quizzes = ref([])
const subjects = ref([])
const loading = ref(true)
const saving = ref(false)
const showModal = ref(false)
const editingId = ref(null)
const formError = ref('')
const activeCategoryIndex = ref(0)
const questionCountInputs = ref(CATEGORY_PRESETS.map(() => 10))

const createEmptyQuestion = (index) => ({
  question: '',
  position: index + 1,
  correct_answer: 'A',
  image_source: '',
  image_url: '',
  image_path: '',
  image: '',
  uploading: false,
  answers: ['A', 'B', 'C', 'D', 'E'].map((label, answerIndex) => ({
    label,
    position: answerIndex + 1,
    answer: '',
  })),
})

const createCategory = (preset) => ({
  ...preset,
  questions: Array.from({ length: 10 }, (_, index) => createEmptyQuestion(index)),
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
  categories: CATEGORY_PRESETS.map((preset) => createCategory(preset)),
})

const form = ref(createForm())
const activeCategory = computed(() => form.value.categories[activeCategoryIndex.value] || null)

const loadData = async () => {
  loading.value = true
  try {
    const [quizRes, subjectRes] = await Promise.all([api.get('/admin/quizzes'), api.get('/subjects')])
    quizzes.value = quizRes.data
    subjects.value = subjectRes.data
  } finally {
    loading.value = false
  }
}

const syncQuestionInputs = () => {
  questionCountInputs.value = form.value.categories.map((category) => category.questions.length)
}

const openCreateModal = () => {
  editingId.value = null
  formError.value = ''
  form.value = createForm()
  activeCategoryIndex.value = 0
  syncQuestionInputs()
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
}

const buildImage = (question) => question.image || question.image_url || question.image_path || ''

const mapCategoryFromResponse = (category, preset) => ({
  label: category.label || preset.label,
  grade_from: category.grade_from ?? preset.grade_from,
  grade_to: category.grade_to ?? preset.grade_to,
  sort_order: category.sort_order ?? preset.sort_order,
  questions: (category.questions || []).map((question, questionIndex) => ({
    question: question.question || '',
    position: question.position ?? questionIndex + 1,
    correct_answer: question.correct_answer || 'A',
    image_source: question.image_source || (question.image_url ? 'url' : question.image_path ? 'upload' : ''),
    image_url: question.image_url || '',
    image_path: question.image_path || '',
    image: question.image || question.image_url || question.image_path || '',
    uploading: false,
    answers: ['A', 'B', 'C', 'D', 'E'].map((label, answerIndex) => {
      const found = (question.answers || []).find((answer) => answer.label === label) || question.answers?.[answerIndex]
      return {
        label,
        position: answerIndex + 1,
        answer: found?.answer || '',
      }
    }),
  })),
})

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
    categories: CATEGORY_PRESETS.map((preset) => {
      const existing = (data.categories || []).find((item) => item.label === preset.label)
      return existing ? mapCategoryFromResponse(existing, preset) : createCategory(preset)
    }),
  }

  activeCategoryIndex.value = 0
  syncQuestionInputs()
  showModal.value = true
}

const applyQuestionCount = (categoryIndex) => {
  const targetCount = Math.min(100, Math.max(1, Number(questionCountInputs.value[categoryIndex]) || 1))
  questionCountInputs.value[categoryIndex] = targetCount

  const category = form.value.categories[categoryIndex]
  category.questions = Array.from({ length: targetCount }, (_, index) => {
    const existing = category.questions[index]
    if (!existing) return createEmptyQuestion(index)

    return {
      ...existing,
      position: index + 1,
      image: buildImage(existing),
      uploading: false,
      answers: ['A', 'B', 'C', 'D', 'E'].map((label, answerIndex) => {
        const answer = existing.answers?.find((item) => item.label === label) || existing.answers?.[answerIndex]
        return {
          label,
          position: answerIndex + 1,
          answer: answer?.answer || '',
        }
      }),
    }
  })
}

const handleImageSourceChange = (question) => {
  if (!question.image_source) {
    question.image_url = ''
    question.image_path = ''
    question.image = ''
    return
  }

  if (question.image_source === 'url') {
    question.image_path = ''
    question.image = question.image_url || ''
    return
  }

  question.image_url = ''
  question.image = question.image_path || ''
}

const uploadQuestionImage = async (event, question) => {
  const [file] = event.target.files || []
  if (!file) return

  question.uploading = true
  formError.value = ''

  try {
    const payload = new FormData()
    payload.append('image', file)

    const { data } = await api.post('/admin/quizzes/upload-image', payload, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    question.image_source = 'upload'
    question.image_path = data.path || ''
    question.image = data.url || data.path || ''
  } catch (error) {
    formError.value = error.response?.data?.message || 'Не удалось загрузить изображение.'
  } finally {
    question.uploading = false
    event.target.value = ''
  }
}

const validateForm = () => {
  if (!form.value.title.trim()) return 'Введите название олимпиады.'
  if (!form.value.subject_id && !form.value.subject.name.trim()) return 'Введите название предмета.'

  for (const category of form.value.categories) {
    if (!category.questions.length) return `Добавьте хотя бы один вопрос в категорию ${category.label}.`

    for (const [index, question] of category.questions.entries()) {
      if (!question.question.trim()) return `Заполните текст вопроса ${index + 1} в категории ${category.label}.`

      if (question.image_source === 'url' && !question.image_url.trim()) {
        return `Укажите ссылку на изображение для вопроса ${index + 1} в категории ${category.label}.`
      }

      if (question.image_source === 'upload' && !question.image_path) {
        return `Загрузите изображение для вопроса ${index + 1} в категории ${category.label}.`
      }

      for (const answer of question.answers) {
        if (!answer.answer.trim()) {
          return `Заполните вариант ${answer.label} в вопросе ${index + 1} категории ${category.label}.`
        }
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
  categories: form.value.categories.map((category, categoryIndex) => ({
    label: category.label,
    grade_from: category.grade_from,
    grade_to: category.grade_to,
    sort_order: category.sort_order ?? categoryIndex + 1,
    questions: category.questions.map((question, questionIndex) => ({
      question: question.question.trim(),
      position: questionIndex + 1,
      correct_answer: question.correct_answer,
      image_source: question.image_source || null,
      image_url: question.image_source === 'url' ? question.image_url.trim() : null,
      image_path: question.image_source === 'upload' ? question.image_path : null,
      answers: question.answers.map((answer, answerIndex) => ({
        label: answer.label,
        position: answerIndex + 1,
        answer: answer.answer.trim(),
      })),
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
  const endpoint = quiz.is_published ? `/admin/quizzes/${quiz.id}/unpublish` : `/admin/quizzes/${quiz.id}/publish`
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
.admin-page { min-height: 100vh; background: radial-gradient(circle at top right, rgba(37,99,235,0.12), transparent 24%), linear-gradient(180deg, #f4f7fb 0%, #e9eef7 100%); padding: 28px; color: #102347; }
.header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; color: #2563eb; font-size: 12px; font-weight: 700; }
h1, h2, h3, h4 { margin: 0; color: #102347; }
.subtext { margin: 10px 0 0; color: #55708f; max-width: 760px; }
.primary-btn, .ghost-btn, .danger-btn { border: 0; border-radius: 16px; padding: 13px 18px; font-weight: 800; cursor: pointer; }
.primary-btn { background: linear-gradient(135deg, #2563eb, #1d4ed8); color: #fff; box-shadow: 0 14px 28px rgba(37,99,235,0.22); }
.ghost-btn { background: #edf4ff; color: #173a72; }
.danger-btn { background: #ffe4e6; color: #be123c; }
.loading-card, .empty-card, .quiz-card, .modal-card { background: rgba(255,255,255,0.94); border: 1px solid rgba(148,163,184,0.2); border-radius: 26px; box-shadow: 0 18px 48px rgba(15,35,85,0.08); }
.loading-card, .empty-card { padding: 28px; }
.quiz-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; }
.quiz-card { padding: 22px; }
.quiz-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.subject-chip, .status-chip, .category-chip, .image-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 12px; font-weight: 700; }
.subject-chip { background: #edf4ff; color: #2563eb; margin-bottom: 10px; }
.status-chip.published { background: #e8f8ed; color: #1f7a34; }
.status-chip.draft { background: #fff7db; color: #9c6b05; }
.quiz-desc { color: #5b6f89; line-height: 1.5; }
.quiz-meta, .quiz-actions, .category-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
.quiz-meta span { color: #55708f; font-size: 14px; }
.category-chip { background: #eff6ff; color: #1d4ed8; }
.modal-backdrop { position: fixed; inset: 0; background: rgba(15,23,42,0.48); padding: 20px; overflow-y: auto; }
.modal-card { width: min(1280px, 100%); margin: 0 auto; padding: 28px; }
.modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.icon-btn { width: 40px; height: 40px; border: 0; border-radius: 14px; background: #edf4ff; color: #102347; font-size: 22px; font-weight: 800; cursor: pointer; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
.field { display: flex; flex-direction: column; gap: 8px; }
.full { grid-column: 1 / -1; }
.field span { color: #36506c; font-size: 13px; font-weight: 700; }
input, textarea, select { width: 100%; border: 1px solid #cad8ea; border-radius: 16px; padding: 13px 15px; font: inherit; background: #fbfdff; color: #102347; outline: none; }
input::placeholder, textarea::placeholder { color: #92a3ba; }
input:focus, textarea:focus, select:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.12); }
.checkbox-field { flex-direction: row; align-items: center; gap: 10px; padding-top: 32px; }
.checkbox-field input, .radio-row input { width: auto; accent-color: #2563eb; }
.categories-toolbar { margin: 24px 0 16px; display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.categories-toolbar p { margin: 8px 0 0; color: #55708f; }
.category-tabs { display: flex; gap: 10px; flex-wrap: wrap; }
.tab-btn { border: 1px solid #c7d8f4; background: #fff; color: #1d4ed8; border-radius: 999px; padding: 10px 14px; font-weight: 700; cursor: pointer; }
.tab-btn.active { background: #1d4ed8; color: #fff; border-color: #1d4ed8; }
.category-editor { border: 1px solid #dbe7f7; border-radius: 24px; padding: 20px; background: linear-gradient(180deg, #f8fbff 0%, #f3f8ff 100%); }
.category-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
.category-heading span { display: block; margin-top: 6px; color: #55708f; font-size: 14px; }
.mini-field { width: 180px; display: flex; flex-direction: column; gap: 8px; }
.mini-field span { color: #36506c; font-size: 13px; font-weight: 700; }
.questions-list { display: grid; gap: 14px; }
.question-card { border: 1px solid #dbe7f7; border-radius: 20px; padding: 18px; background: rgba(255,255,255,0.88); }
.question-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.question-title { font-weight: 700; color: #102347; }
.image-badge { background: #ecfeff; color: #0f766e; }
.image-tools { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
.upload-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.upload-btn { display: inline-flex; align-items: center; gap: 8px; background: #eff6ff; color: #1d4ed8; border-radius: 14px; padding: 12px 14px; font-weight: 700; cursor: pointer; width: fit-content; }
.upload-btn input { display: none; }
.upload-note { color: #55708f; font-size: 13px; }
.image-preview { border: 1px solid #dbe7f7; border-radius: 18px; padding: 12px; background: #fff; }
.image-preview img { display: block; width: 100%; max-height: 240px; object-fit: contain; border-radius: 12px; background: #f8fbff; }
.answers-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.radio-row { display: flex; align-items: center; gap: 8px; color: #55708f; font-size: 13px; }
.error-text { margin: 16px 0 0; color: #b91c1c; font-weight: 600; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
@media (max-width: 900px) { .form-grid, .answers-grid, .image-tools { grid-template-columns: 1fr; } .mini-field { width: 100%; } }
@media (max-width: 640px) { .admin-page, .modal-backdrop { padding: 16px; } .header, .modal-head, .categories-toolbar, .modal-actions, .category-top, .question-head { flex-direction: column; } .modal-actions .primary-btn, .modal-actions .ghost-btn, .header .primary-btn, .upload-btn { width: 100%; justify-content: center; } }
</style>
