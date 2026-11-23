import { Editor, Extension, Node, Mark } from '@tiptap/core';
import StarterKit from '@tiptap/starter-kit';
import { FileHandler } from '@tiptap/extension-file-handler';
import { Link } from '@tiptap/extension-link';
import { Image } from '@tiptap/extension-image';
import { Table, TableRow, TableCell, TableHeader } from '@tiptap/extension-table';

window.Tiptap = { Editor, Extension, Node, Mark, StarterKit, FileHandler, Link, Image, Table, TableRow, TableCell, TableHeader };
