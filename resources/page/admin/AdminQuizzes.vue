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
  price: 2990,
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
    price: data.price ?? 2990,
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
* { box-sizing: border-box; }
.admin-page { min-height: 100vh; background: radial-gradient(circle at top right, rgba(201,171,99,0.12), transparent 24%), linear-gradient(180deg, var(--bg) 0%, var(--bg-alt) 100%); padding: 28px; color: var(--text); }
.header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.header-actions { display: flex; gap: 10px; align-items: center; flex-wrap: wrap; }
.eyebrow { margin: 0 0 8px; text-transform: uppercase; letter-spacing: 0.08em; color: var(--accent-strong); font-size: 0.75rem; font-weight: 700; }
h1, h2, h3, h4 { margin: 0; color: var(--text); }
.subtext { margin: 10px 0 0; color: var(--text-secondary); max-width: 760px; }
.primary-btn, .ghost-btn, .danger-btn { border: 0; border-radius: 16px; padding: 13px 18px; min-height: 2.75rem; font-weight: 800; cursor: pointer; }
.primary-btn { background: linear-gradient(135deg, var(--accent) 0%, #e2c171 100%); color: var(--text); box-shadow: 0 14px 28px rgba(201,171,99,0.22); }
.ghost-btn { background: rgba(201,171,99,0.12); color: var(--accent-strong); }
.danger-btn { background: var(--danger-bg); color: #8f3b3b; }
.loading-card, .empty-card, .quiz-card, .modal-card { background: var(--surface); border: 1px solid var(--surface-border); border-radius: 26px; box-shadow: var(--shadow-card); }
.loading-card, .empty-card { padding: 28px; }
.quiz-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; }
.quiz-card { padding: 22px; }
.quiz-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.subject-chip, .status-chip, .category-chip, .image-badge { display: inline-flex; padding: 6px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; }
.subject-chip { background: rgba(201,171,99,0.16); color: var(--accent-strong); margin-bottom: 10px; }
.status-chip.published { background: var(--success-bg); color: #2f6f4b; }
.status-chip.draft { background: var(--warning-bg); color: var(--accent-strong); }
.quiz-desc { color: var(--text-secondary); line-height: 1.5; }
.quiz-meta, .quiz-actions, .category-list { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 16px; }
.quiz-meta span { color: var(--text-secondary); font-size: 0.875rem; }
.category-chip { background: rgba(201,171,99,0.14); color: var(--accent-strong); }
.modal-backdrop { position: fixed; inset: 0; background: rgba(39,30,12,0.28); padding: 20px; overflow-y: auto; }
.modal-card { width: min(1280px, 100%); margin: 0 auto; padding: 28px; }
.modal-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 20px; }
.icon-btn { width: 2.75rem; height: 2.75rem; min-height: 2.75rem; border: 0; border-radius: 14px; background: rgba(201,171,99,0.12); color: var(--text); font-size: 1.375rem; font-weight: 800; cursor: pointer; }
.form-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
.field { display: flex; flex-direction: column; gap: 8px; }
.full { grid-column: 1 / -1; }
.field span { color: var(--text-secondary); font-size: 0.8125rem; font-weight: 700; }
input, textarea, select { width: 100%; border: 1px solid var(--surface-border); border-radius: 16px; padding: 13px 15px; font: inherit; background: rgba(255,252,245,.95); color: var(--text); outline: none; }
input::placeholder, textarea::placeholder { color: color-mix(in srgb, var(--text-secondary) 78%, white 22%); }
input:focus, textarea:focus, select:focus { border-color: color-mix(in srgb, var(--accent) 75%, white 25%); box-shadow: 0 0 0 4px rgba(201,171,99,0.16); }
.checkbox-field { flex-direction: row; align-items: center; gap: 10px; padding-top: 32px; }
.checkbox-field input, .radio-row input { width: auto; accent-color: var(--accent); }
.categories-toolbar { margin: 24px 0 16px; display: flex; align-items: flex-end; justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.categories-toolbar p { margin: 8px 0 0; color: var(--text-secondary); }
.category-tabs { display: flex; gap: 10px; flex-wrap: wrap; }
.tab-btn { border: 1px solid rgba(201,171,99,0.22); background: rgba(255,252,244,.88); color: var(--accent-strong); border-radius: 999px; padding: 10px 14px; font-weight: 700; cursor: pointer; }
.tab-btn.active { background: rgba(201,171,99,0.18); color: var(--text); border-color: rgba(201,171,99,0.32); }
.category-editor { border: 1px solid var(--surface-border); border-radius: 24px; padding: 20px; background: linear-gradient(180deg, rgba(255,252,244,.88) 0%, rgba(249,242,226,.92) 100%); }
.category-top { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; flex-wrap: wrap; }
.category-heading span { display: block; margin-top: 6px; color: var(--text-secondary); font-size: 0.875rem; }
.mini-field { width: min(180px, 100%); display: flex; flex-direction: column; gap: 8px; }
.mini-field span { color: var(--text-secondary); font-size: 0.8125rem; font-weight: 700; }
.questions-list { display: grid; gap: 14px; }
.question-card { border: 1px solid var(--surface-border); border-radius: 20px; padding: 18px; background: rgba(255,252,244,0.88); }
.question-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
.question-title { font-weight: 700; color: var(--text); }
.image-badge { background: var(--success-bg); color: #2f6f4b; }
.image-tools { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; margin-bottom: 14px; }
.upload-row { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.upload-btn { display: inline-flex; align-items: center; gap: 8px; background: rgba(79,167,116,0.1); color: #316a49; border-radius: 14px; padding: 12px 14px; font-weight: 700; cursor: pointer; width: fit-content; }
.upload-btn input { display: none; }
.upload-note { color: var(--text-secondary); font-size: 13px; }
.image-preview { border: 1px solid var(--surface-border); border-radius: 18px; padding: 12px; background: rgba(255,252,244,.92); }
.image-preview img { display: block; width: 100%; max-height: 240px; object-fit: contain; border-radius: 12px; background: rgba(255,252,244,.8); }
.answers-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
.answer-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; margin-top: 12px; }
.answer-count { color: var(--text-secondary); font-size: 13px; font-weight: 700; }
.empty-category-text { margin: 0; color: var(--text-secondary); font-weight: 600; }
.small-btn { padding: 10px 14px; border-radius: 12px; }
.radio-row { display: flex; align-items: center; gap: 8px; color: var(--text-secondary); font-size: 13px; }
.error-text { margin: 16px 0 0; color: #8f3b3b; font-weight: 600; }
.modal-actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px; }
.category-top-right { display: flex; align-items: flex-end; gap: 10px; flex-wrap: wrap; }
.import-doc-btn { background: rgba(79,167,116,0.1); color: #316a49; border: 0; border-radius: 16px; padding: 13px 16px; font-weight: 800; cursor: pointer; white-space: nowrap; }
.import-doc-btn:hover { background: rgba(79,167,116,0.18); }
.import-backdrop { z-index: 100; }
.import-modal { max-width: 700px; }
.import-upload-area { border: 2px dashed var(--surface-border); border-radius: 20px; padding: 24px; text-align: center; margin: 16px 0; }
.import-file-label { display: inline-flex; align-items: center; gap: 10px; background: rgba(201,171,99,0.12); color: var(--accent-strong); border-radius: 14px; padding: 13px 18px; font-weight: 700; cursor: pointer; }
.import-file-label input { display: none; }
.import-file-label.label-disabled { opacity: 0.6; cursor: not-allowed; }
.answer-key-box { margin-top: 16px; display: grid; gap: 10px; }
.answer-key-actions { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.import-warnings { background: rgba(201,171,99,0.08); border: 1px solid rgba(201,171,99,0.28); border-radius: 16px; padding: 14px 18px; margin-top: 12px; }
.warning-title { margin: 0 0 8px; font-weight: 700; color: var(--accent-strong); font-size: 13px; }
.import-warnings ul { margin: 0; padding: 0 0 0 18px; color: var(--text-secondary); font-size: 13px; line-height: 1.6; }
.import-preview { margin-top: 16px; }
.import-count { margin: 0 0 10px; font-weight: 700; }
.import-preview-list { max-height: 340px; overflow-y: auto; display: grid; gap: 8px; }
.import-preview-item { display: flex; gap: 12px; align-items: flex-start; background: rgba(255,252,244,.9); border: 1px solid var(--surface-border); border-radius: 14px; padding: 12px 14px; }
.import-q-num { font-weight: 800; color: var(--accent-strong); min-width: 22px; font-size: 14px; }
.import-q-body { flex: 1; min-width: 0; }
.import-q-text { margin: 0 0 6px; font-size: 14px; line-height: 1.4; word-break: break-word; }
.import-q-badges { display: flex; flex-wrap: wrap; gap: 6px; }
.no-correct-badge { background: rgba(143,59,59,0.1); color: #8f3b3b; border-radius: 999px; padding: 3px 8px; font-size: 11px; font-weight: 700; }
.answer-count-badge { background: rgba(201,171,99,0.14); color: var(--accent-strong); border-radius: 999px; padding: 3px 8px; font-size: 11px; font-weight: 700; }
@media (max-width: 900px) { .form-grid, .answers-grid, .image-tools { grid-template-columns: 1fr; } .mini-field { width: 100%; } }
@media (max-width: 640px) { .admin-page, .modal-backdrop { padding: 16px; } .header, .modal-head, .categories-toolbar, .modal-actions, .category-top, .question-head { flex-direction: column; } .modal-actions .primary-btn, .modal-actions .ghost-btn, .header .primary-btn, .upload-btn { width: 100%; justify-content: center; } .category-top-right { width: 100%; } }

/* Drop zone */
.image-dropzone {
  grid-column: 1 / -1;
  border: 2px dashed rgba(201,171,99,0.35);
  border-radius: 18px;
  padding: 20px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 10px;
  min-height: 120px;
  background: rgba(255,252,244,0.7);
  cursor: pointer;
  transition: border-color 0.18s, background 0.18s, box-shadow 0.18s;
  outline: none;
  text-align: center;
  position: relative;
}
.image-dropzone:focus-visible {
  border-color: var(--accent);
  box-shadow: 0 0 0 4px rgba(201,171,99,0.18);
}
.image-dropzone.dropzone--dragging {
  border-color: var(--accent);
  background: rgba(201,171,99,0.1);
  box-shadow: 0 0 0 4px rgba(201,171,99,0.18);
}
.image-dropzone.dropzone--filled {
  border-style: solid;
  border-color: rgba(201,171,99,0.25);
  padding: 12px;
  background: rgba(255,252,244,0.9);
}
.image-dropzone.dropzone--uploading {
  border-style: solid;
  background: rgba(201,171,99,0.06);
  pointer-events: none;
}

.dropzone-icon-wrap {
  color: rgba(201,171,99,0.7);
}
.dropzone-label {
  margin: 0;
  font-weight: 700;
  color: var(--text);
  font-size: 0.9rem;
}
.dropzone-sublabel {
  margin: 0;
  color: var(--text-secondary);
  font-size: 0.8rem;
}
.dropzone-label kbd {
  background: rgba(201,171,99,0.18);
  border: 1px solid rgba(201,171,99,0.35);
  border-radius: 6px;
  padding: 1px 6px;
  font: inherit;
  font-size: 0.82em;
  color: var(--accent-strong);
}

.dropzone-file-btn {
  display: inline-flex;
  align-items: center;
  gap: 6px;
  background: rgba(201,171,99,0.14);
  color: var(--accent-strong);
  border-radius: 12px;
  padding: 8px 14px;
  font-weight: 700;
  font-size: 0.85rem;
  cursor: pointer;
  margin-top: 4px;
  transition: background 0.15s;
}
.dropzone-file-btn:hover { background: rgba(201,171,99,0.22); }
.dropzone-file-btn input { display: none; }

.dropzone-preview-img {
  display: block;
  width: 100%;
  max-height: 220px;
  object-fit: contain;
  border-radius: 10px;
}
.dropzone-remove-btn {
  position: absolute;
  top: 10px;
  right: 10px;
  background: rgba(143,59,59,0.12);
  color: #8f3b3b;
  border: 0;
  border-radius: 10px;
  padding: 6px 10px;
  font-size: 0.8rem;
  font-weight: 700;
  cursor: pointer;
  transition: background 0.15s;
}
.dropzone-remove-btn:hover { background: rgba(143,59,59,0.22); }

.dropzone-msg {
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.9rem;
}

@keyframes spin { to { transform: rotate(360deg); } }
.dropzone-spinner {
  width: 28px;
  height: 28px;
  border: 3px solid rgba(201,171,99,0.25);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.75s linear infinite;
}
</style>
