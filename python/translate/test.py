import subprocess
import shutil

unoconv_path = shutil.which("unoconv")
docx_path="/Volumes/data/erui/ezwork-api/storage/app/public/translate/9WHoPMLc5Acl503WVzfF3eV3V4evq0xN35SnAJv6/avm-中文.docx"
pdf_path="/Volumes/data/erui/ezwork-api/storage/app/public/translate/9WHoPMLc5Acl503WVzfF3eV3V4evq0xN35SnAJv6/avm-中文.pdf"

print(unoconv_path)
print("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path))
subprocess.run("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path), shell=True)
