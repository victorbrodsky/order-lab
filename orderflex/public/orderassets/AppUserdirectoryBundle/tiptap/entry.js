import { Editor, Extension, Node, Mark } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { FileHandler } from '@tiptap/extension-file-handler';
import { Link } from '@tiptap/extension-link';
import { Image } from '@tiptap/extension-image';

window.Tiptap = { Editor, Extension, Node, Mark, StarterKit, FileHandler, Link, Image };
