<template>
  <div class="rich-editor" :class="{ 'rich-editor--invalid': invalid }">
    <div class="rich-toolbar">
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive('bold') }" @click="editor?.chain().focus().toggleBold().run()" title="Bold">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/><path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"/></svg>
      </button>
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive('italic') }" @click="editor?.chain().focus().toggleItalic().run()" title="Italic">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="4" x2="10" y2="4"/><line x1="14" y1="20" x2="5" y2="20"/><line x1="15" y1="4" x2="9" y2="20"/></svg>
      </button>
      <div class="tb-sep"></div>
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive('bulletList') }" @click="editor?.chain().focus().toggleBulletList().run()" title="Bullet list">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="9" y1="6" x2="20" y2="6"/><line x1="9" y1="12" x2="20" y2="12"/><line x1="9" y1="18" x2="20" y2="18"/><circle cx="4" cy="6" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="12" r="1.5" fill="currentColor" stroke="none"/><circle cx="4" cy="18" r="1.5" fill="currentColor" stroke="none"/></svg>
      </button>
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive('orderedList') }" @click="editor?.chain().focus().toggleOrderedList().run()" title="Numbered list">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="10" y1="6" x2="21" y2="6"/><line x1="10" y1="12" x2="21" y2="12"/><line x1="10" y1="18" x2="21" y2="18"/><text x="1" y="9" font-size="7" fill="currentColor" stroke="none" font-weight="700">1</text><text x="1" y="15" font-size="7" fill="currentColor" stroke="none" font-weight="700">2</text><text x="1" y="21" font-size="7" fill="currentColor" stroke="none" font-weight="700">3</text></svg>
      </button>
      <div class="tb-sep"></div>
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive({ textAlign: 'left' }) }" @click="editor?.chain().focus().setTextAlign('left').run()" title="Align left">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="15" y2="12"/><line x1="3" y1="18" x2="18" y2="18"/></svg>
      </button>
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive({ textAlign: 'center' }) }" @click="editor?.chain().focus().setTextAlign('center').run()" title="Align center">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="6" y1="12" x2="18" y2="12"/><line x1="4" y1="18" x2="20" y2="18"/></svg>
      </button>
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive({ textAlign: 'right' }) }" @click="editor?.chain().focus().setTextAlign('right').run()" title="Align right">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="9" y1="12" x2="21" y2="12"/><line x1="6" y1="18" x2="21" y2="18"/></svg>
      </button>
      <div class="tb-sep"></div>
      <button type="button" class="tb-btn" :class="{ 'tb-btn--active': editor?.isActive('link') }" @click="setLink" title="Add hyperlink">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
      </button>
      <button v-if="editor?.isActive('link')" type="button" class="tb-btn" @click="editor?.chain().focus().unsetLink().run()" title="Remove link">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/><line x1="2" y1="2" x2="22" y2="22" stroke-width="2"/></svg>
      </button>
    </div>
    <editor-content :editor="editor" class="rich-editor-content" />
  </div>
</template>

<script setup>
import { useEditor, EditorContent } from '@tiptap/vue-3'
import StarterKit from '@tiptap/starter-kit'
import Link from '@tiptap/extension-link'
import TextAlign from '@tiptap/extension-text-align'
import Placeholder from '@tiptap/extension-placeholder'

const props = defineProps({
  modelValue: { type: String, default: '' },
  invalid: { type: Boolean, default: false },
  placeholder: { type: String, default: '' }
})

const emit = defineEmits(['update:modelValue'])

const editor = useEditor({
  content: props.modelValue,
  extensions: [
    StarterKit,
    Link.configure({ openOnClick: false, HTMLAttributes: { target: '_blank', rel: 'noopener noreferrer' } }),
    TextAlign.configure({ types: ['heading', 'paragraph'] }),
    Placeholder.configure({ placeholder: props.placeholder })
  ],
  onUpdate({ editor }) {
    const html = editor.getHTML()
    emit('update:modelValue', html === '<p></p>' ? '' : html)
  }
})

watch(() => props.modelValue, (val) => {
  if (!editor.value) return
  const current = editor.value.getHTML()
  const normalized = current === '<p></p>' ? '' : current
  if (val !== normalized) {
    editor.value.commands.setContent(val || '', false)
  }
})

onBeforeUnmount(() => editor.value?.destroy())

function setLink() {
  const prev = editor.value?.getAttributes('link').href || ''
  const url = window.prompt('Enter URL:', prev)
  if (url === null) return
  if (!url) {
    editor.value?.chain().focus().unsetLink().run()
    return
  }
  const href = /^https?:\/\//i.test(url) ? url : `https://${url}`
  editor.value?.chain().focus().extendMarkRange('link').setLink({ href }).run()
}
</script>

<style scoped>
.rich-editor {
  border: 1px solid #d1d5db;
  border-radius: 8px;
  overflow: hidden;
  background: #fff;
  transition: border-color 0.15s;
}
.rich-editor:focus-within { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,0.12); }
.rich-editor--invalid { border-color: #ef4444; }

.rich-toolbar {
  display: flex;
  align-items: center;
  gap: 2px;
  padding: 6px 8px;
  border-bottom: 1px solid #e5e7eb;
  background: #f9fafb;
  flex-wrap: wrap;
}
.tb-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 28px;
  height: 28px;
  border: none;
  background: transparent;
  border-radius: 5px;
  cursor: pointer;
  color: #6b7280;
  transition: background 0.12s, color 0.12s;
}
.tb-btn:hover { background: #e5e7eb; color: #111827; }
.tb-btn--active { background: #d1fae5; color: #059669; }
.tb-sep { width: 1px; height: 18px; background: #e5e7eb; margin: 0 3px; }

.rich-editor-content { padding: 10px 12px; min-height: 90px; }

.rich-editor-content :deep(.tiptap) {
  outline: none;
  font-size: 13.5px;
  line-height: 1.6;
  color: #1f2937;
}
.rich-editor-content :deep(.tiptap p) { margin: 0 0 4px; }
.rich-editor-content :deep(.tiptap p:last-child) { margin-bottom: 0; }
.rich-editor-content :deep(.tiptap ul),
.rich-editor-content :deep(.tiptap ol) { padding-left: 20px; margin: 4px 0; }
.rich-editor-content :deep(.tiptap li) { margin: 2px 0; }
.rich-editor-content :deep(.tiptap a) { color: #2563eb; text-decoration: underline; cursor: pointer; }
.rich-editor-content :deep(.tiptap p.is-editor-empty:first-child::before) {
  content: attr(data-placeholder);
  color: #9ca3af;
  pointer-events: none;
  float: left;
  height: 0;
}
</style>
