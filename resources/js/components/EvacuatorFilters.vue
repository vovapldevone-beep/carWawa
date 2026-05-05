<script setup>
import { computed, ref, watch } from 'vue'

const TOW_TYPES = [
  {
    value: 'Платформа',
    label: 'Платформа',
    icon: 'platform',
  },
  {
    value: 'Частковий погрузник',
    label: 'Частковий погрузник',
    icon: 'partial',
  },
  {
    value: 'Кран-маніпулятор',
    label: 'Кран-маніпулятор',
    icon: 'crane',
  },
]

const CAPACITY_OPTIONS = [
  { min: null, label: 'Будь-яка' },
  { min: 3, label: 'до 3 т' },
  { min: 3.5, label: 'до 3.5 т' },
  { min: 5, label: 'до 5 т' },
  { min: 7, label: 'до 7 т' },
  { min: 10, label: 'до 10 т' },
]

const props = defineProps({
  filteredCount: {
    type: Number,
    default: 0,
  },
})

const address = defineModel('address', { type: String, default: '' })
const radiusKm = defineModel('radiusKm', { type: Number, default: 1 })

const emit = defineEmits([
  'update:filters',
  'geocode',
  'save-zone',
  'show-results',
])

const selectedType = ref('')
const capacityIndex = ref(0)

const capacityMin = computed(() => {
  const idx = Number(capacityIndex.value)
  const opt = CAPACITY_OPTIONS[Number.isFinite(idx) ? idx : 0]
  return opt?.min ?? null
})

const workingNow = ref(false)
const onlineBooking = ref(false)

const emitFilters = () => {
  const min = capacityMin.value
  emit('update:filters', {
    typeTow: selectedType.value === '' ? null : selectedType.value,
    minLoadCapacity:
      min != null && !Number.isNaN(min) && min > 0 ? min : null,
  })
}

watch([selectedType, capacityIndex], emitFilters, { immediate: true })

const selectType = (value) => {
  selectedType.value = selectedType.value === value ? '' : value
}

const onGeocodeClick = () => {
  emit('geocode')
}

const onSaveZone = () => {
  emit('save-zone')
}

const onShowResults = () => {
  emit('show-results')
}
</script>

<template>
  <div class="evf" aria-label="Пошук евакуатора">
    <header class="evf__intro">
      <h2 class="evf__title">Пошук евакуатора</h2>
      <p class="evf__subtitle">Оберіть параметри та зону на карті</p>
    </header>

    <div class="evf__group">
      <label class="evf__label evf__label--caps">Локація</label>
      <div class="evf__location">
        <span class="evf__pin" aria-hidden="true">
          <svg class="evf__pin-svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
          </svg>
        </span>
        <input
          v-model="address"
          type="text"
          class="evf__location-input"
          placeholder="Київ, вул. Хрещатик, 1"
          autocomplete="street-address"
          @keydown.enter.prevent="onGeocodeClick"
        />
        <button type="button" class="evf__btn evf__btn--geocode" @click="onGeocodeClick">
          Знайти
        </button>
      </div>
    </div>

    <div class="evf__group">
      <div class="evf__row">
        <label class="evf__label">Радіус пошуку</label>
        <span class="evf__radius-badge">{{ radiusKm }} км</span>
      </div>
      <input
        v-model.number="radiusKm"
        type="range"
        min="1"
        max="50"
        step="1"
        class="evf__range"
      />
      <div class="evf__range-hints">
        <span>1 км</span>
        <span>50 км</span>
      </div>
    </div>

    <div class="evf__group">
      <span class="evf__label">Тип евакуатора</span>
      <div class="evf__types">
        <button
          v-for="item in TOW_TYPES"
          :key="item.value"
          type="button"
          class="evf__type"
          :class="{ 'evf__type--active': selectedType === item.value }"
          @click="selectType(item.value)"
        >
          <span class="evf__type-icon" aria-hidden="true">
            <svg
              v-if="item.icon === 'platform'"
              class="evf__type-svg"
              viewBox="0 0 56 32"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M4 22h48v4H4v-4z" fill="currentColor" opacity="0.25" />
              <rect x="6" y="14" width="36" height="8" rx="1" fill="currentColor" opacity="0.45" />
              <circle cx="12" cy="24" r="4" fill="currentColor" />
              <circle cx="44" cy="24" r="4" fill="currentColor" />
              <path d="M38 10l10 4v6H38V10z" fill="currentColor" opacity="0.35" />
            </svg>
            <svg
              v-else-if="item.icon === 'partial'"
              class="evf__type-svg"
              viewBox="0 0 56 32"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M4 20h30v6H4v-6z" fill="currentColor" opacity="0.35" />
              <circle cx="12" cy="24" r="4" fill="currentColor" />
              <path d="M28 18l8-8 12 6v8H28v-6z" fill="currentColor" opacity="0.45" />
              <circle cx="22" cy="26" r="3" fill="currentColor" opacity="0.5" />
            </svg>
            <svg
              v-else
              class="evf__type-svg"
              viewBox="0 0 56 32"
              xmlns="http://www.w3.org/2000/svg"
            >
              <path d="M4 22h32v4H4v-4z" fill="currentColor" opacity="0.35" />
              <circle cx="12" cy="24" r="4" fill="currentColor" />
              <path d="M30 6 L46 4 L44 20 L32 18 Z" fill="currentColor" opacity="0.4" />
              <path d="M32 18v8h8v-4" stroke="currentColor" stroke-width="2" fill="none" />
            </svg>
          </span>
          <span class="evf__type-label">{{ item.label }}</span>
        </button>
      </div>
    </div>

    <div class="evf__group">
      <label class="evf__label" for="evf-capacity">Вантажопідйомність</label>
      <div class="evf__select-wrap">
        <select id="evf-capacity" v-model="capacityIndex" class="evf__select">
          <option
            v-for="(opt, idx) in CAPACITY_OPTIONS"
            :key="idx"
            :value="idx"
          >
            {{ opt.label }}
          </option>
        </select>
        <span class="evf__select-chevron" aria-hidden="true">
          <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
          </svg>
        </span>
      </div>
    </div>

    <div class="evf__switches">
      <div class="evf__switch-row">
        <span class="evf__switch-text">Працює зараз</span>
        <button
          type="button"
          role="switch"
          :aria-checked="workingNow"
          class="evf__switch"
          :class="{ 'evf__switch--on': workingNow }"
          @click="workingNow = !workingNow"
        >
          <span class="evf__switch-knob" />
        </button>
      </div>
      <div class="evf__switch-row">
        <span class="evf__switch-text">Онлайн бронювання</span>
        <button
          type="button"
          role="switch"
          :aria-checked="onlineBooking"
          class="evf__switch"
          :class="{ 'evf__switch--on': onlineBooking }"
          @click="onlineBooking = !onlineBooking"
        >
          <span class="evf__switch-knob" />
        </button>
      </div>
    </div>

    <div class="evf__actions">
      <button type="button" class="evf__btn evf__btn--primary" @click="onShowResults">
        Показати {{ props.filteredCount }} евакуаторів
      </button>
      <button type="button" class="evf__btn evf__btn--secondary" @click="onSaveZone">
        Зберегти поточну зону
      </button>
    </div>
  </div>
</template>

<style scoped>
.evf {
  --evf-primary: #0066ff;
  --evf-primary-hover: #0052cc;
  --evf-border: #e2e8f0;
  --evf-text: #0f172a;
  --evf-muted: #64748b;
  --evf-bg-soft: #f8fafc;

  display: flex;
  flex-direction: column;
  gap: 1.25rem;
  font-family: ui-sans-serif, system-ui, -apple-system, 'Segoe UI', Roboto, sans-serif;
  color: var(--evf-text);
}

.evf__intro {
  margin: 0;
}

.evf__title {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
  letter-spacing: -0.02em;
}

.evf__subtitle {
  margin: 0.25rem 0 0;
  font-size: 0.8125rem;
  color: var(--evf-muted);
}

.evf__group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.evf__label {
  font-size: 0.875rem;
  font-weight: 500;
  color: #334155;
}

.evf__label--caps {
  font-size: 0.6875rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--evf-muted);
}

.evf__location {
  display: flex;
  align-items: stretch;
  overflow: hidden;
  border-radius: 0.75rem;
  border: 1px solid var(--evf-border);
  background: #fff;
  box-shadow: 0 1px 2px rgb(15 23 42 / 0.05);
}

.evf__location:focus-within {
  border-color: rgb(0 102 255 / 0.45);
  box-shadow: 0 0 0 3px rgb(0 102 255 / 0.15);
}

.evf__pin {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 0 0.75rem;
  background: var(--evf-bg-soft);
  border-right: 1px solid #f1f5f9;
  color: #94a3b8;
  flex-shrink: 0;
}

.evf__pin-svg {
  width: 1.25rem;
  height: 1.25rem;
}

.evf__location-input {
  flex: 1;
  min-width: 0;
  border: none;
  padding: 0.625rem 0.75rem;
  font-size: 0.875rem;
  background: transparent;
  color: var(--evf-text);
}

.evf__location-input::placeholder {
  color: #94a3b8;
}

.evf__location-input:focus {
  outline: none;
}

.evf__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
}

.evf__radius-badge {
  font-size: 0.875rem;
  font-weight: 600;
  font-variant-numeric: tabular-nums;
  color: var(--evf-primary);
  background: #f1f5f9;
  padding: 0.125rem 0.5rem;
  border-radius: 0.5rem;
}

.evf__range {
  width: 100%;
  height: 0.375rem;
  border-radius: 999px;
  background: #e2e8f0;
  cursor: pointer;
  accent-color: var(--evf-primary);
}

.evf__range-hints {
  display: flex;
  justify-content: space-between;
  font-size: 0.6875rem;
  color: #94a3b8;
}

.evf__types {
  display: grid;
  grid-template-columns: 1fr;
  gap: 0.5rem;
}

@media (min-width: 520px) {
  .evf__types {
    grid-template-columns: repeat(3, 1fr);
  }
}

.evf__type {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  border: 2px solid var(--evf-border);
  background: #fff;
  cursor: pointer;
  text-align: center;
  transition:
    border-color 0.15s,
    box-shadow 0.15s,
    background 0.15s;
}

.evf__type:hover {
  border-color: #cbd5e1;
  box-shadow: 0 1px 3px rgb(15 23 42 / 0.06);
}

.evf__type--active {
  border-color: var(--evf-primary);
  background: rgb(0 102 255 / 0.06);
  box-shadow: 0 1px 3px rgb(15 23 42 / 0.08);
}

.evf__type-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  height: 3rem;
  width: 100%;
  color: #475569;
}

.evf__type-svg {
  width: 3.5rem;
  height: 2rem;
}

.evf__type-label {
  font-size: 0.6875rem;
  font-weight: 500;
  line-height: 1.25;
  color: #334155;
}

@media (min-width: 520px) {
  .evf__type-label {
    font-size: 0.75rem;
  }
}

.evf__select-wrap {
  position: relative;
}

.evf__select {
  width: 100%;
  appearance: none;
  border-radius: 0.75rem;
  border: 1px solid var(--evf-border);
  background: #fff;
  padding: 0.625rem 2.25rem 0.625rem 0.75rem;
  font-size: 0.875rem;
  color: var(--evf-text);
  box-shadow: 0 1px 2px rgb(15 23 42 / 0.04);
}

.evf__select:focus {
  outline: none;
  border-color: rgb(0 102 255 / 0.5);
  box-shadow: 0 0 0 3px rgb(0 102 255 / 0.15);
}

.evf__select-chevron {
  position: absolute;
  right: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  color: #94a3b8;
}

.evf__switches {
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  border: 1px solid #f1f5f9;
  background: rgb(248 250 252 / 0.9);
}

.evf__switch-row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.75rem;
}

.evf__switch-text {
  font-size: 0.875rem;
  color: #334155;
}

.evf__switch {
  position: relative;
  width: 2.75rem;
  height: 1.75rem;
  flex-shrink: 0;
  border: none;
  border-radius: 999px;
  background: #cbd5e1;
  cursor: pointer;
  padding: 0;
  transition: background 0.15s;
}

.evf__switch--on {
  background: var(--evf-primary);
}

.evf__switch-knob {
  position: absolute;
  top: 0.25rem;
  left: 0.25rem;
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 50%;
  background: #fff;
  box-shadow: 0 1px 2px rgb(0 0 0 / 0.15);
  transition: transform 0.15s;
}

.evf__switch--on .evf__switch-knob {
  transform: translateX(1.25rem);
}

.evf__actions {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding-top: 0.25rem;
}

.evf__btn {
  display: block;
  width: 100%;
  border: none;
  border-radius: 0.75rem;
  font-size: 0.875rem;
  font-weight: 600;
  cursor: pointer;
  transition:
    background 0.15s,
    transform 0.05s;
}

.evf__btn:active {
  transform: scale(0.99);
}

.evf__btn--geocode {
  flex-shrink: 0;
  padding: 0 1rem;
  background: var(--evf-primary);
  color: #fff;
  font-weight: 500;
  border-radius: 0;
  width: auto;
}

.evf__btn--geocode:hover {
  background: var(--evf-primary-hover);
}

.evf__btn--primary {
  padding: 0.75rem 1rem;
  background: var(--evf-primary);
  color: #fff;
  box-shadow: 0 4px 12px rgb(0 102 255 / 0.25);
}

.evf__btn--primary:hover {
  background: var(--evf-primary-hover);
}

.evf__btn--secondary {
  padding: 0.625rem 1rem;
  background: #fff;
  color: #334155;
  border: 2px solid var(--evf-border);
  font-weight: 500;
}

.evf__btn--secondary:hover {
  border-color: #cbd5e1;
  background: var(--evf-bg-soft);
}

.evf__range::-webkit-slider-thumb {
  appearance: none;
  width: 1.125rem;
  height: 1.125rem;
  border-radius: 50%;
  background: var(--evf-primary);
  border: 2px solid #fff;
  box-shadow: 0 1px 4px rgb(0 0 0 / 0.2);
  margin-top: -0.3125rem;
}

.evf__range::-moz-range-thumb {
  width: 1.125rem;
  height: 1.125rem;
  border-radius: 50%;
  background: var(--evf-primary);
  border: 2px solid #fff;
  box-shadow: 0 1px 4px rgb(0 0 0 / 0.2);
}
</style>
