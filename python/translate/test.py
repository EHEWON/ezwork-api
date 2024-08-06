import subprocess
import shutil

unoconv_path = shutil.which("unoconv")
docx_path="/Volumes/data/erui/ezwork-api/storage/app/public/translate/ehBTbaeCbdMPJdV7B9lV1zewG3KM5H7agVk6KIIQ/avm-1-俄语.docx"
pdf_path="/Volumes/data/erui/ezwork-api/storage/app/public/translate/ehBTbaeCbdMPJdV7B9lV1zewG3KM5H7agVk6KIIQ/avm-1-俄语.pdf"

print("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path))
process=subprocess.run("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path), stdout=subprocess.PIPE, text=True)
print(process.stdout)
print("done")


subprocess.run("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path))
print("done2")

subprocess.run("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path),shell=True)
print("done3")