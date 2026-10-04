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
      <div class="header-actions">
        <label class="ghost-btn" :class="{ 'btn-loading': importingQuiz }" style="cursor:pointer">
          <input type="file" accept=".json" style="display:none" @change="importQuiz" :disabled="importingQuiz" />
          {{ importingQuiz ? 'Импорт...' : 'Импортировать олимпиаду' }}
        </label>
        <button class="primary-btn" @click="openCreateModal">Новая олимпиада</button>
      </div>
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
          <span>{{ formatPrice(quiz.price) }}</span>
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
          <button class="ghost-btn" @click="exportQuiz(quiz)">Экспорт</button>
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
            <span>Цена участия, ₸</span>
            <input v-model.number="form.price" type="number" min="0" step="1" />
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
            <label class="field">
              <span>Дата завершения</span>
              <input v-model="form.subject.end_date" type="date" />
            </label>
            <label class="field full">
              <span>Описание предмета</span>
              <textarea v-model="form.subject.description" rows="2"></textarea>
            </label>
            <div class="field full">
              <span>Баннер предмета</span>
              <div class="upload-row">
                <label class="upload-btn">
                  <input type="file" accept="image/*" @change="uploadSubjectImage" />
                  <span>{{ form.subject.uploading ? 'Загрузка...' : 'Загрузить изображение с устройства' }}</span>
                </label>
                <span class="upload-note">
                  {{ form.subject.image_path ? 'Файл загружен' : 'PNG, JPG, WEBP до 4 МБ' }}
                </span>
              </div>
              <div v-if="form.subject.image" class="image-preview subject-preview">
                <img :src="form.subject.image" alt="Превью баннера предмета" />
              </div>
            </div>
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

            <div class="category-top-right">
            <label class="mini-field">
              <span>Количество вопросов</span>
              <input
                v-model.number="questionCountInputs[activeCategoryIndex]"
                type="number"
                min="0"
                max="100"
                @change="applyQuestionCount(activeCategoryIndex)"
              />
            </label>
            <button type="button" class="import-doc-btn" @click="openImportModal(activeCategoryIndex)">
              Загрузить из документа
            </button>
          </div>
          </div>

          <p v-if="!activeCategory.questions.length" class="empty-category-text">
            Для этого диапазона вопросы пока не добавлены. Укажите количество больше `0`, если хотите включить эту категорию в олимпиаду.
          </p>

          <div v-else class="questions-list">
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

              <label class="field full">
                <span>Обоснование правильного ответа</span>
                <textarea
                  v-model="question.explanation"
                  rows="3"
                  placeholder="Коротко объясните, почему этот ответ верный."
                ></textarea>
              </label>

              <div class="image-tools">
                <div
                  class="image-dropzone"
                  :class="{ 'dropzone--dragging': question._dragging, 'dropzone--uploading': question.uploading, 'dropzone--filled': question.image && !question.uploading }"
                  tabindex="0"
                  role="button"
                  :aria-label="`Зона загрузки изображения для вопроса ${qIndex + 1}`"
                  @paste.stop="handlePasteImage($event, question)"
                  @dragover.prevent="question._dragging = true"
                  @dragleave.prevent="question._dragging = false"
                  @drop.prevent="handleDropImage($event, question)"
                >
                  <template v-if="question.uploading">
                    <div class="dropzone-spinner"></div>
                    <span class="dropzone-msg">Загружаем изображение...</span>
                  </template>
                  <template v-else-if="question.image">
                    <img :src="question.image" :alt="`Вопрос ${qIndex + 1}`" class="dropzone-preview-img" />
                    <button type="button" class="dropzone-remove-btn" @click.stop="clearQuestionImage(question)">✕ Удалить</button>
                  </template>
                  <template v-else>
                    <div class="dropzone-icon-wrap">
                      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                    </div>
                    <p class="dropzone-label">Перетащите или нажмите <kbd>Ctrl+V</kbd></p>
                    <p class="dropzone-sublabel">PNG, JPG, WEBP до 5 МБ</p>
                    <label class="dropzone-file-btn">
                      <input type="file" accept="image/png,image/jpeg,image/webp" @change="uploadQuestionImage($event, question)" />
                      <span>Выбрать файл</span>
                    </label>
                  </template>
                </div>

                <label class="field full">
                  <span>Или ссылка на изображение</span>
                  <input
                    v-model="question.image_url"
                    type="url"
                    placeholder="https://example.com/image.jpg"
                    @input="onImageUrlInput(question)"
                  />
                </label>
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
              <div class="answer-actions">
                <span class="answer-count">Вариантов ответа: {{ question.answers.length }}</span>
                <button
                  type="button"
                  class="ghost-btn small-btn"
                  :disabled="question.answers.length >= 8"
                  @click="addAnswerOption(question)"
                >
                  Добавить вариант
                </button>
                <button
                  type="button"
                  class="ghost-btn small-btn"
                  :disabled="question.answers.length <= 2"
                  @click="removeAnswerOption(question)"
                >
                  Удалить последний
                </button>
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

  <div v-if="showImportModal" class="modal-backdrop import-backdrop" @click.self="closeImportModal">
    <div class="modal-card import-modal">
      <div class="modal-head">
        <div>
          <p class="eyebrow">Import</p>
          <h2>Импорт вопросов из документа</h2>
          <p v-if="importCategoryIndex !== null" class="subtext">
            Категория: <strong>{{ form.categories[importCategoryIndex]?.label }}</strong>
          </p>
        </div>
        <button class="icon-btn" @click="closeImportModal">×</button>
      </div>

      <div class="import-upload-area">
        <label class="import-file-label" :class="{ 'label-disabled': importing }">
          <input type="file" accept=".docx,.pdf" @change="handleDocumentUpload" :disabled="importing" />
          <span v-if="importing">Обработка документа...</span>
          <span v-else-if="importPreview.length">Загрузить другой файл</span>
          <span v-else>Выберите DOCX или PDF файл</span>
        </label>
        <p class="upload-note">Поддерживаются Word (.docx) и PDF, до 20 МБ</p>
      </div>

      <div class="answer-key-box">
        <label class="field full">
          <span>Ключ правильных ответов</span>
          <textarea
            v-model="importAnswerKey"
            rows="8"
            placeholder="1. C&#10;2. A&#10;3. B&#10;4. B"
          ></textarea>
        </label>
        <p class="upload-note">Вставьте ответы в формате `1. C`, `2. A` или `5) А`.</p>
        <div class="answer-key-actions">
          <button class="ghost-btn" type="button" :disabled="!importPreview.length || !importAnswerKey.trim()" @click="applyAnswerKeyToPreview">
            Применить ключ
          </button>
          <span v-if="importPreview.length" class="upload-note">Найдено вопросов для автозаполнения: {{ importPreview.length }}</span>
        </div>
      </div>

      <p v-if="importError" class="error-text" style="white-space: pre-wrap;">{{ importError }}</p>

      <div v-if="importWarnings.length" class="import-warnings">
        <p class="warning-title">Предупреждения ({{ importWarnings.length }}):</p>
        <ul>
          <li v-for="(w, wi) in importWarnings" :key="wi">{{ w }}</li>
        </ul>
      </div>

      <div v-if="importPreview.length" class="import-preview">
        <p class="import-count">Найдено вопросов: <strong>{{ importPreview.length }}</strong></p>
        <div class="import-preview-list">
          <div v-for="(q, qi) in importPreview" :key="qi" class="import-preview-item">
            <span class="import-q-num">{{ qi + 1 }}</span>
            <div class="import-q-body">
              <p class="import-q-text">{{ q.question }}</p>
              <div class="import-q-badges">
                <span v-if="!q.correct_answer" class="no-correct-badge">Нет правильного ответа</span>
                <span v-if="q.image_path" class="image-badge">С картинкой</span>
                <span class="answer-count-badge">{{ q.answers.length }} вариантов</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="modal-actions">
        <button class="ghost-btn" @click="closeImportModal">Отмена</button>
        <button
          class="primary-btn"
          :disabled="!importPreview.length || importing"
          @click="applyImport"
        >
          Добавить {{ importPreview.length }} {{ importPreview.length === 1 ? 'вопрос' : importPreview.length < 5 ? 'вопроса' : 'вопросов' }}
        </button>
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
const questionCountInputs = ref(CATEGORY_PRESETS.map(() => 0))
const ANSWER_LABELS = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'.split('')

const showImportModal = ref(false)
const importCategoryIndex = ref(null)
const importing = ref(false)
const importError = ref('')
const importPreview = ref([])
const importWarnings = ref([])
const importAnswerKey = ref('')

const importingQuiz = ref(false)

const createAnswerOption = (answerIndex, answer = {}) => ({
  label: answer.label || ANSWER_LABELS[answerIndex] || `Option ${answerIndex + 1}`,
  position: answer.position ?? answerIndex + 1,
  answer: answer.answer || '',
})

const createEmptyQuestion = (index) => ({
  question: '',
  explanation: '',
  position: index + 1,
  correct_answer: 'A',
  image_source: '',
  image_url: '',
  image_path: '',
  image: '',
  uploading: false,
  _dragging: false,
  answers: Array.from({ length: 4 }, (_, answerIndex) => createAnswerOption(answerIndex)),
})

const createCategory = (preset) => ({
  ...preset,
  questions: [],
})

const createForm = () => ({
  subject_id: null,
  subject: {
    name: '',
    description: '',
    image: '',
    image_path: '',
    uploading: false,
    start_date: '',
    end_date: '',
  },
  title: '',
  description: '',
  price: 2000,
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

const normalizeAnswers = (answers = []) => {
  const normalized = answers.map((answer, answerIndex) => createAnswerOption(answerIndex, answer))
  return normalized.length ? normalized : Array.from({ length: 4 }, (_, answerIndex) => createAnswerOption(answerIndex))
}

const getFilledCategories = (categories = form.value.categories) =>
  categories.filter((category) => category.questions.length > 0)

const mapCategoryFromResponse = (category, preset) => ({
  label: category.label || preset.label,
  grade_from: category.grade_from ?? preset.grade_from,
  grade_to: category.grade_to ?? preset.grade_to,
  sort_order: category.sort_order ?? preset.sort_order,
  questions: (category.questions || []).map((question, questionIndex) => ({
    question: question.question || '',
    explanation: question.explanation || '',
    position: question.position ?? questionIndex + 1,
    correct_answer: question.correct_answer || 'A',
    image_source: question.image_source || (question.image_url ? 'url' : question.image_path ? 'upload' : ''),
    image_url: question.image_url || '',
    image_path: question.image_path || '',
    image: question.image || question.image_url || question.image_path || '',
    uploading: false,
    _dragging: false,
    answers: normalizeAnswers(question.answers || []),
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
      image_path: '',
      uploading: false,
      start_date: data.subject?.start_date || '',
      end_date: data.subject?.end_date || '',
    },
    title: data.title,
    description: data.description ?? '',
    price: data.price ?? 2000,
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
  const targetCount = Math.min(100, Math.max(0, Number(questionCountInputs.value[categoryIndex]) || 0))
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
      _dragging: false,
      answers: normalizeAnswers(existing.answers),
    }
  })
}

const reindexAnswers = (question) => {
  question.answers = question.answers.map((answer, answerIndex) => ({
    ...answer,
    label: ANSWER_LABELS[answerIndex] || answer.label || `Option ${answerIndex + 1}`,
    position: answerIndex + 1,
  }))

  if (!question.answers.some((answer) => answer.label === question.correct_answer)) {
    question.correct_answer = question.answers[0]?.label || 'A'
  }
}

const addAnswerOption = (question) => {
  if (question.answers.length >= 8) return
  question.answers.push(createAnswerOption(question.answers.length))
  reindexAnswers(question)
}

const removeAnswerOption = (question) => {
  if (question.answers.length <= 2) return
  question.answers.pop()
  reindexAnswers(question)
}

const ALLOWED_IMAGE_TYPES = ['image/png', 'image/jpeg', 'image/webp']
const MAX_IMAGE_BYTES = 5 * 1024 * 1024

const clearQuestionImage = (question) => {
  question.image_source = ''
  question.image_path = ''
  question.image_url = ''
  question.image = ''
}

const uploadFileToQuestion = async (file, question) => {
  if (!ALLOWED_IMAGE_TYPES.includes(file.type)) {
    formError.value = 'Поддерживаются форматы PNG, JPG, WEBP.'
    return
  }
  if (file.size > MAX_IMAGE_BYTES) {
    formError.value = 'Изображение не должно превышать 5 МБ.'
    return
  }

  // Instant preview while uploading
  const previewUrl = URL.createObjectURL(file)
  question.image = previewUrl
  question.uploading = true
  formError.value = ''

  try {
    const payload = new FormData()
    payload.append('image', file)
    const { data } = await api.post('/admin/quizzes/upload-image', payload, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    question.image_source = 'upload'
    question.image_path = data.path || ''
    question.image = data.url || data.path || ''
  } catch (error) {
    question.image = ''
    question.image_source = ''
    question.image_path = ''
    formError.value = error.response?.data?.message || 'Не удалось загрузить изображение.'
  } finally {
    question.uploading = false
    URL.revokeObjectURL(previewUrl)
  }
}

const uploadQuestionImage = async (event, question) => {
  const [file] = event.target.files || []
  if (!file) return
  await uploadFileToQuestion(file, question)
  event.target.value = ''
}

const handlePasteImage = async (event, question) => {
  const items = Array.from(event.clipboardData?.items || [])
  const imageItem = items.find((item) => item.type.startsWith('image/'))
  if (!imageItem) return
  event.preventDefault()
  const file = imageItem.getAsFile()
  if (file) await uploadFileToQuestion(file, question)
}

const handleDropImage = async (event, question) => {
  question._dragging = false
  const file = event.dataTransfer?.files?.[0]
  if (!file || !file.type.startsWith('image/')) return
  await uploadFileToQuestion(file, question)
}

const onImageUrlInput = (question) => {
  if (question.image_url.trim()) {
    question.image_source = 'url'
    question.image_path = ''
    question.image = question.image_url
  } else {
    question.image_source = ''
    question.image = ''
  }
}

const uploadSubjectImage = async (event) => {
  const [file] = event.target.files || []
  if (!file) return

  form.value.subject.uploading = true
  formError.value = ''

  try {
    const payload = new FormData()
    payload.append('image', file)

    const { data } = await api.post('/admin/quizzes/upload-subject-image', payload, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })

    form.value.subject.image_path = data.path || ''
    form.value.subject.image = data.url || ''
  } catch (error) {
    formError.value = error.response?.data?.message || 'Не удалось загрузить баннер предмета.'
  } finally {
    form.value.subject.uploading = false
    event.target.value = ''
  }
}

const validateForm = () => {
  if (!form.value.title.trim()) return 'Введите название олимпиады.'
  if (!form.value.subject_id && !form.value.subject.name.trim()) return 'Введите название предмета.'
  if (!Number.isInteger(Number(form.value.price)) || Number(form.value.price) < 0) return 'Укажите корректную цену участия.'

  const filledCategories = getFilledCategories()

  if (!filledCategories.length) {
    return 'Добавьте хотя бы одну категорию с вопросами.'
  }

  for (const category of filledCategories) {

    for (const [index, question] of category.questions.entries()) {
      if (!question.question.trim()) return `Заполните текст вопроса ${index + 1} в категории ${category.label}.`

      if (question.image_source === 'url' && !question.image_url.trim()) {
        return `Укажите ссылку на изображение для вопроса ${index + 1} в категории ${category.label}.`
      }

      if (question.image_source === 'upload' && !question.image_path) {
        return `Загрузите изображение для вопроса ${index + 1} в категории ${category.label}.`
      }

      if (question.answers.length < 2) {
        return `Добавьте минимум два варианта ответа в вопрос ${index + 1} категории ${category.label}.`
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
        name: form.value.subject.name,
        description: form.value.subject.description,
        image: form.value.subject.image_path || form.value.subject.image,
        start_date: form.value.subject.start_date || new Date().toISOString().slice(0, 10),
        end_date: form.value.subject.end_date || null,
      },
  title: form.value.title.trim(),
  description: form.value.description.trim(),
  price: Number(form.value.price) || 0,
  time_limit: Number(form.value.time_limit) || 60,
  is_published: !!form.value.is_published,
  categories: getFilledCategories().map((category, categoryIndex) => ({
    label: category.label,
    grade_from: category.grade_from,
    grade_to: category.grade_to,
    sort_order: category.sort_order ?? categoryIndex + 1,
    questions: category.questions.map((question, questionIndex) => ({
      question: question.question.trim(),
      explanation: question.explanation?.trim() || '',
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
  const filledCategories = getFilledCategories()
  if (!filledCategories.length) {
    formError.value = 'Добавьте хотя бы одну категорию с вопросами.'
    return
  }

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

const formatPrice = (price) => new Intl.NumberFormat('ru-RU').format(Number(price) || 0) + ' ₸'

const deleteQuiz = async (id) => {
  if (!window.confirm('Удалить олимпиаду?')) return
  await api.delete(`/admin/quizzes/${id}`)
  await loadData()
}

const openImportModal = (catIndex) => {
  importCategoryIndex.value = catIndex
  importError.value = ''
  importPreview.value = []
  importWarnings.value = []
  importAnswerKey.value = ''
  showImportModal.value = true
}

const closeImportModal = () => {
  showImportModal.value = false
  importAnswerKey.value = ''
}

const normalizeAnswerLabel = (value = '') => {
  const normalized = String(value).trim().toUpperCase()
  const cyrillicMap = {
    'А': 'A',
    'В': 'B',
    'С': 'C',
    'Д': 'D',
    'Е': 'E',
    'Ф': 'F',
  }

  return cyrillicMap[normalized] || normalized
}

const parseAnswerKey = (raw) =>
  raw
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter(Boolean)
    .map((line) => {
      const match = line.match(/^(\d+)\s*[\.\)\-:]?\s*([A-Za-zА-Яа-я])$/u)
      if (!match) return null

      return {
        index: Number(match[1]) - 1,
        label: normalizeAnswerLabel(match[2]),
      }
    })
    .filter(Boolean)

const applyAnswerKeyToPreview = () => {
  const parsedAnswers = parseAnswerKey(importAnswerKey.value)

  if (!parsedAnswers.length) {
    importError.value = 'Не удалось разобрать ключ ответов. Используйте формат вида: 1. C'
    return
  }

  importError.value = ''
  const answerMap = new Map(parsedAnswers.map((item) => [item.index, item.label]))

  importPreview.value = importPreview.value.map((question, index) => ({
    ...question,
    correct_answer: answerMap.get(index) || question.correct_answer || '',
  }))
}

const handleDocumentUpload = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  importing.value = true
  importError.value = ''
  importPreview.value = []
  importWarnings.value = []

  try {
    const fd = new FormData()
    fd.append('file', file)
    const { data } = await api.post('/admin/quizzes/parse-document', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    importPreview.value = data.questions || []
    importWarnings.value = data.warnings || []
    if (importAnswerKey.value.trim()) {
      applyAnswerKeyToPreview()
    }
  } catch (e) {
    const msg = e?.response?.data?.message || 'Ошибка при разборе документа.'
    const detail = e?.response?.data?.detail
    importError.value = detail ? `${msg}\n\nДетали: ${detail}` : msg
  } finally {
    importing.value = false
    event.target.value = ''
  }
}

const applyImport = () => {
  const cat = form.value.categories[importCategoryIndex.value]
  const startPos = cat.questions.length + 1

  importPreview.value.forEach((q, i) => {
    cat.questions.push({
      question: q.question,
      explanation: q.explanation || '',
      position: startPos + i,
      correct_answer: q.correct_answer || '',
      image_source: q.image_source || '',
      image_url: q.image_url || '',
      image_path: q.image_path || '',
      image: q.image_preview_url || q.image_url || '',
      uploading: false,
      answers: q.answers.map((a, ai) => ({
        label: a.label,
        answer: a.answer,
        position: ai + 1,
      })),
    })
  })

  questionCountInputs.value[importCategoryIndex.value] = cat.questions.length
  closeImportModal()
}

const exportQuiz = async (quiz) => {
  try {
    const { data, headers } = await api.get(`/admin/quizzes/${quiz.id}/export`, { responseType: 'blob' })
    const cd = headers['content-disposition'] || ''
    const nameMatch = cd.match(/filename="([^"]+)"/)
    const filename = nameMatch ? nameMatch[1] : `quiz-${quiz.id}.json`
    const url = URL.createObjectURL(new Blob([data], { type: 'application/json' }))
    const a = document.createElement('a')
    a.href = url
    a.download = filename
    a.click()
    URL.revokeObjectURL(url)
  } catch {
    alert('Не удалось экспортировать олимпиаду.')
  }
}

const importQuiz = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return
  importingQuiz.value = true
  try {
    const fd = new FormData()
    fd.append('file', file)
    const { data } = await api.post('/admin/quizzes/import-json', fd, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
    await loadData()
    alert(`Олимпиада "${data.title}" успешно импортирована (${data.questions_count} вопросов).`)
  } catch (e) {
    const msg = e?.response?.data?.message || 'Ошибка при импорте.'
    const detail = e?.response?.data?.detail
    alert(detail ? `${msg}\n\n${detail}` : msg)
  } finally {
    importingQuiz.value = false
    event.target.value = ''
  }
}

onMounted(loadData)
</script>

<style scoped>
.btn-loading { opacity: 0.6; pointer-events: none; }

/* ---- Список олимпиад ---- */
.quiz-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
  gap: 14px;
}

.quiz-card {
  display: grid;
  align-content: start;
  gap: 12px;
  padding: 18px;
  border-radius: var(--radius-lg);
  background: var(--card);
  border: 1px solid var(--border);
}

.quiz-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.quiz-head h2 { margin-top: 8px; font-size: 17px; }

.subject-chip { background: var(--brand-soft); color: var(--brand-ink); }

.quiz-desc {
  color: var(--text-secondary);
  font-size: 14px;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.quiz-meta {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.quiz-meta span,
.category-chip {
  padding: 3px 9px;
  border-radius: var(--radius-xs);
  background: var(--bg);
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 500;
  font-variant-numeric: tabular-nums;
}

.category-list {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
}

.quiz-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  padding-top: 12px;
  border-top: 1px solid var(--border);
}

.empty-card h2 { color: var(--text); margin-bottom: 4px; }

/* ---- Форма олимпиады ---- */
.modal-card { width: min(1040px, 100%); }

.form-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 12px;
}

.field {
  display: grid;
  gap: 6px;
  align-content: start;
}

.field > span {
  font-size: 13.5px;
  font-weight: 600;
}

.field.full { grid-column: 1 / -1; }

.checkbox-field {
  display: flex;
  align-items: center;
  gap: 10px;
  align-self: end;
  min-height: 42px;
  padding: 0 12px;
  border-radius: var(--radius-sm);
  background: var(--bg);
}

.upload-row {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 10px;
}

.upload-btn input,
.dropzone-file-btn input,
.import-file-label input { display: none; }

.upload-note {
  color: var(--text-tertiary);
  font-size: 13px;
}

.image-preview {
  margin-top: 8px;
  padding: 8px;
  border-radius: var(--radius-md);
  background: var(--bg);
}

.image-preview img {
  max-height: 180px;
  margin: 0 auto;
  border-radius: var(--radius-sm);
  object-fit: contain;
}

/* ---- Категории ---- */
.categories-toolbar {
  display: grid;
  gap: 10px;
  padding-top: 14px;
  border-top: 1px solid var(--border);
}

.categories-toolbar h3 {
  font-family: var(--font-sans);
  font-size: 17px;
  font-weight: 700;
  letter-spacing: 0;
}

.categories-toolbar p {
  color: var(--text-secondary);
  font-size: 14px;
}

.category-tabs {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  padding: 4px;
  border-radius: var(--radius-sm);
  background: var(--bg-alt);
}

.tab-btn {
  min-height: 36px;
  padding: 6px 12px;
  border: 0;
  border-radius: 9px;
  background: transparent;
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.tab-btn.active {
  background: var(--card);
  color: var(--brand-ink);
  box-shadow: var(--shadow-sm);
}

.category-editor {
  display: grid;
  gap: 14px;
  padding: 16px;
  border-radius: var(--radius-lg);
  background: var(--bg);
}

.category-top {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  justify-content: space-between;
  gap: 12px;
}

.category-heading h4 {
  font-size: 18px;
  font-weight: 700;
}

.category-heading span {
  color: var(--text-secondary);
  font-size: 14px;
}

.category-top-right {
  display: flex;
  flex-wrap: wrap;
  align-items: flex-end;
  gap: 10px;
}

.mini-field {
  display: grid;
  gap: 4px;
  width: 180px;
}

.mini-field span { font-size: 13px; font-weight: 600; }

.import-doc-btn {
  min-height: 42px;
  padding: 8px 14px;
  border: 1.5px dashed var(--brand);
  border-radius: var(--radius-sm);
  background: var(--brand-softer);
  color: var(--brand-ink);
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
}

.import-doc-btn:hover { background: var(--brand-soft); }

.empty-category-text {
  padding: 16px;
  border-radius: var(--radius-sm);
  border: 1.5px dashed var(--border-strong);
  color: var(--text-secondary);
  font-size: 14px;
}

.questions-list {
  display: grid;
  gap: 12px;
}

.question-card {
  display: grid;
  gap: 12px;
  padding: 16px;
  border-radius: var(--radius-md);
  background: var(--card);
  border: 1px solid var(--border);
}

.question-head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 10px;
}

.question-title {
  font-weight: 700;
  color: var(--brand-ink);
}

.image-badge,
.answer-count-badge,
.no-correct-badge {
  padding: 2px 8px;
  border-radius: var(--radius-xs);
  font-size: 12px;
  font-weight: 600;
}

.image-badge { background: var(--tone-violet-soft); color: var(--tone-violet); }
.answer-count-badge { background: var(--bg-alt); color: var(--text-secondary); }
.no-correct-badge { background: var(--danger-soft); color: var(--danger-ink); }

/* ---- Загрузка картинки ---- */
.image-tools {
  display: grid;
  grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
  gap: 12px;
  align-items: start;
}

.image-dropzone {
  position: relative;
  display: grid;
  justify-items: center;
  align-content: center;
  gap: 4px;
  min-height: 150px;
  padding: 14px;
  border-radius: var(--radius-md);
  border: 2px dashed var(--border-strong);
  background: var(--bg);
  text-align: center;
  transition: border-color var(--dur) ease, background-color var(--dur) ease;
}

.image-dropzone:focus-visible { outline: none; box-shadow: var(--focus-ring); }
.image-dropzone.dropzone--dragging { border-color: var(--brand); background: var(--brand-softer); }
.image-dropzone.dropzone--filled { border-style: solid; border-color: var(--border); }

.dropzone-icon-wrap {
  width: 48px;
  height: 48px;
  display: grid;
  place-items: center;
  border-radius: 14px;
  background: var(--card);
  color: var(--brand);
}

.dropzone-label { font-size: 14px; font-weight: 600; }
.dropzone-sublabel { color: var(--text-tertiary); font-size: 12.5px; }

kbd {
  padding: 1px 5px;
  border-radius: 5px;
  border: 1px solid var(--border-strong);
  background: var(--card);
  font-family: ui-monospace, Consolas, monospace;
  font-size: 12px;
}

.dropzone-file-btn {
  margin-top: 6px;
  padding: 6px 12px;
  border-radius: var(--radius-xs);
  background: var(--brand-soft);
  color: var(--brand-ink);
  font-size: 13.5px;
  font-weight: 600;
  cursor: pointer;
}

.dropzone-preview-img {
  max-height: 140px;
  border-radius: var(--radius-sm);
  object-fit: contain;
}

.dropzone-remove-btn {
  margin-top: 6px;
  padding: 4px 10px;
  border: 0;
  border-radius: var(--radius-xs);
  background: var(--danger-soft);
  color: var(--danger-ink);
  font-size: 13px;
  font-weight: 600;
  cursor: pointer;
}

.dropzone-spinner {
  width: 26px;
  height: 26px;
  border-radius: 50%;
  border: 3px solid color-mix(in srgb, var(--brand) 25%, transparent);
  border-top-color: var(--brand);
  animation: spin 0.8s linear infinite;
}

.dropzone-msg { color: var(--text-secondary); font-size: 13.5px; }

@keyframes spin { to { transform: rotate(360deg); } }

/* ---- Варианты ответа ---- */
.answers-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  gap: 10px;
}

.radio-row {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  color: var(--text-secondary);
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
}

.radio-row:has(input:checked) { color: var(--success-ink); font-weight: 600; }

.answer-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}

.answer-count {
  margin-right: auto;
  color: var(--text-secondary);
  font-size: 13.5px;
}

.error-text {
  padding: 10px 14px;
  border-radius: var(--radius-sm);
  background: var(--danger-soft);
  color: var(--danger-ink);
  font-size: 14px;
}

/* ---- Импорт из документа (окно вне .admin-page) ---- */
.import-modal {
  width: min(820px, 100%);
  max-height: calc(100dvh - 32px);
  display: grid;
  gap: 16px;
  padding: 24px;
  overflow-y: auto;
  border-radius: var(--radius-xl);
  background: var(--card);
  color: var(--text);
  box-shadow: var(--shadow-lg);
}

.import-modal .modal-head {
  display: flex;
  align-items: flex-start;
  justify-content: space-between;
  gap: 12px;
}

.import-modal .eyebrow { color: var(--brand-ink); font-size: 13.5px; font-weight: 600; }
.import-modal h2 { font-family: var(--font-sans); font-size: 20px; font-weight: 700; }
.import-modal .subtext { margin-top: 4px; color: var(--text-secondary); font-size: 14.5px; }

.import-modal .icon-btn {
  width: 38px;
  height: 38px;
  display: grid;
  place-items: center;
  border-radius: var(--radius-sm);
  border: 1px solid var(--border);
  background: var(--card);
  color: var(--text-secondary);
  font-size: 18px;
  cursor: pointer;
}

.import-upload-area {
  display: grid;
  justify-items: center;
  gap: 6px;
  padding: 20px;
  border-radius: var(--radius-md);
  border: 2px dashed var(--border-strong);
  background: var(--bg);
  text-align: center;
}

.import-file-label {
  padding: 10px 18px;
  border-radius: var(--radius-sm);
  background: var(--brand);
  color: var(--on-brand);
  font-weight: 600;
  cursor: pointer;
}

.import-file-label.label-disabled { opacity: 0.6; pointer-events: none; }

.answer-key-box {
  display: grid;
  gap: 8px;
}

.import-modal textarea {
  width: 100%;
  min-height: 140px;
  padding: 10px 12px;
  border-radius: var(--radius-sm);
  border: 1.5px solid var(--border-strong);
  background: var(--input-bg);
  color: var(--text);
  font-family: ui-monospace, Consolas, monospace;
  font-size: 14px;
}

.answer-key-actions,
.import-modal .modal-actions {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 8px;
}

.import-modal .modal-actions { justify-content: flex-end; }

.import-modal :is(.ghost-btn, .primary-btn) {
  min-height: 42px;
  padding: 8px 16px;
  border-radius: var(--radius-sm);
  border: 1.5px solid var(--border-strong);
  background: var(--card);
  color: var(--text);
  font-size: 14.5px;
  font-weight: 600;
  cursor: pointer;
}

.import-modal .primary-btn { background: var(--brand); border-color: var(--brand); color: var(--on-brand); }
.import-modal :is(.ghost-btn, .primary-btn):disabled { opacity: 0.5; cursor: not-allowed; }

.import-warnings {
  padding: 12px 14px;
  border-radius: var(--radius-sm);
  background: var(--warning-soft);
  color: var(--warning-ink);
  font-size: 13.5px;
}

.import-warnings ul { margin-top: 4px; padding-left: 18px; }
.warning-title { font-weight: 700; }

.import-preview { display: grid; gap: 8px; }
.import-count { font-size: 14.5px; }

.import-preview-list {
  display: grid;
  gap: 6px;
  max-height: 320px;
  overflow-y: auto;
}

.import-preview-item {
  display: grid;
  grid-template-columns: 32px minmax(0, 1fr);
  gap: 10px;
  padding: 10px;
  border-radius: var(--radius-sm);
  background: var(--bg);
}

.import-q-num {
  width: 32px;
  height: 32px;
  display: grid;
  place-items: center;
  border-radius: 10px;
  background: var(--card);
  color: var(--brand-ink);
  font-size: 13px;
  font-weight: 700;
}

.import-q-text { font-size: 14px; overflow-wrap: anywhere; }

.import-q-badges {
  display: flex;
  flex-wrap: wrap;
  gap: 4px;
  margin-top: 4px;
}

@media (max-width: 860px) {
  .form-grid,
  .image-tools,
  .answers-grid { grid-template-columns: 1fr; }
  .quiz-grid { grid-template-columns: 1fr; }
}
</style>
