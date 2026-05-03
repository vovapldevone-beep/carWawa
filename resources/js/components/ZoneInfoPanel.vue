<script setup>
defineProps({
  visible: {
    type: Boolean,
    required: true,
  },
  zone: {
    type: Object,
    default: null,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  errorMessage: {
    type: String,
    default: null,
  },
})

const emit = defineEmits(['close'])

const displayText = (value) => {
  if (value === null || value === undefined) {
    return ''
  }
  return String(value).trim()
}

const siteHref = (site) => {
  const s = displayText(site)
  if (!s) {
    return '#'
  }
  return /^https?:\/\//i.test(s) ? s : `https://${s}`
}
</script>

<template>
  <aside
    v-show="visible"
    class="zone-info-panel"
    aria-label="Інформація про зону"
  >
    <div class="zone-info-panel__header">
      <h2 class="zone-info-panel__title">Зона</h2>
      <button
        type="button"
        class="zone-info-panel__close"
        aria-label="Закрити"
        @click="emit('close')"
      >
        ×
      </button>
    </div>

    <div v-if="loading" class="zone-info-panel__state">
      Завантаження…
    </div>
    <div v-else-if="errorMessage" class="zone-info-panel__state zone-info-panel__state--error">
      {{ errorMessage }}
    </div>
    <dl v-else-if="zone" class="zone-info-panel__dl">
      <dt>Назва</dt>
      <dd>{{ displayText(zone.name) || '—' }}</dd>
      <dt>Номер</dt>
      <dd>{{ displayText(zone.number) || '—' }}</dd>
      <dt>Сайт</dt>
      <dd>
        <a
          v-if="displayText(zone.site)"
          class="zone-info-panel__link"
          :href="siteHref(zone.site)"
          target="_blank"
          rel="noopener noreferrer"
        >{{ displayText(zone.site) }}</a>
        <span v-else>—</span>
      </dd>
      <dt>Адреса</dt>
      <dd>{{ displayText(zone.address) || '—' }}</dd>
      <dt>Радіус</dt>
      <dd>{{ zone.radius }} м</dd>
      <dt>Тип евакуатора</dt>
      <dd>{{ displayText(zone.type_tow) || '—' }}</dd>
      <dt>Вантажопідйомність</dt>
      <dd>
        <template v-if="zone.load_capacity != null && zone.load_capacity !== ''">
          {{ zone.load_capacity }} т
        </template>
        <template v-else>—</template>
      </dd>
    </dl>
  </aside>
</template>

<style scoped>
.zone-info-panel {
  width: min(320px, 100%);
  flex-shrink: 0;
  border-left: 1px solid #e0e0e0;
  background: #fafafa;
  padding: 1rem;
  overflow: auto;
}

.zone-info-panel__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1rem;
}

.zone-info-panel__title {
  margin: 0;
  font-size: 1.125rem;
  font-weight: 600;
}

.zone-info-panel__close {
  border: none;
  background: transparent;
  font-size: 1.5rem;
  line-height: 1;
  cursor: pointer;
  color: #666;
  padding: 0.25rem 0.5rem;
}

.zone-info-panel__close:hover {
  color: #111;
}

.zone-info-panel__state {
  color: #555;
  font-size: 0.9375rem;
}

.zone-info-panel__state--error {
  color: #b00020;
}

.zone-info-panel__dl {
  margin: 0;
}

.zone-info-panel__dl dt {
  font-size: 0.75rem;
  text-transform: uppercase;
  letter-spacing: 0.04em;
  color: #666;
  margin-top: 1rem;
}

.zone-info-panel__dl dt:first-child {
  margin-top: 0;
}

.zone-info-panel__dl dd {
  margin: 0.35rem 0 0;
  font-size: 1rem;
  color: #111;
}

.zone-info-panel__link {
  color: #1565c0;
  word-break: break-all;
}

.zone-info-panel__link:hover {
  text-decoration: underline;
}
</style>
