import { readdir, rename, stat, unlink, writeFile } from 'node:fs/promises'
import path from 'node:path'
import sharp from 'sharp'

const root = path.resolve('public/images')

async function walk(dir) {
  const entries = await readdir(dir, { withFileTypes: true })
  const files = []

  for (const entry of entries) {
    const fullPath = path.join(dir, entry.name)
    if (entry.isDirectory()) {
      files.push(...await walk(fullPath))
    } else if (/\.(jpe?g|png)$/i.test(entry.name)) {
      files.push(fullPath)
    }
  }

  return files
}

async function optimizeFile(filePath) {
  const before = (await stat(filePath)).size
  const ext = path.extname(filePath).toLowerCase()
  const image = sharp(filePath).rotate()

  let output
  if (ext === '.png') {
    output = await image.png({ compressionLevel: 9 }).toBuffer()
  } else {
    output = await image.jpeg({ quality: 82, mozjpeg: true }).toBuffer()
  }

  if (output.length >= before) {
    return
  }

  const tempPath = `${filePath}.tmp`
  await writeFile(tempPath, output)
  await unlink(filePath)
  await rename(tempPath, filePath)

  const after = (await stat(filePath)).size
  const saved = (((before - after) / before) * 100).toFixed(1)
  console.log(`optimized ${path.relative(process.cwd(), filePath)} (-${saved}%)`)
}

const files = await walk(root)
for (const file of files) {
  try {
    await optimizeFile(file)
  } catch (error) {
    console.error(`failed ${path.relative(process.cwd(), file)}:`, error.message)
  }
}

console.log(`Done. Checked ${files.length} images.`)
