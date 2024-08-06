import subprocess
import shutil
import getpass

# unoconv_path = shutil.which("unoconv")
unoconv_path = "sudo unoconv"
docx_path="/data/www/ezwork_api/storage/app/public/translate/ehBTbaeCbdMPJdV7B9lV1zewG3KM5H7agVk6KIIQ/avm-1-俄语.docx"
pdf_path="/data/www/ezwork_api/storage/app/public/translate/ehBTbaeCbdMPJdV7B9lV1zewG3KM5H7agVk6KIIQ/avm-1-俄语.pdf"

username = getpass.getuser()
print(f"当前执行用户是: {username}\n")

print("\n{} -f pdf -o {} {}\n".format(unoconv_path, pdf_path, docx_path))
try:
    process=subprocess.run("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path), stdout=subprocess.PIPE, text=True)
    print(process.stdout)
    print("done\n")
except Exception as e:
    print(e)

try:
    print("\nstart2")
    subprocess.run("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path))
    print("\ndone2")
except Exception as e:
    print(e)

try:
    print("\nstart3")
    subprocess.run("{} -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path),shell=True)
    print("\ndone3")
except Exception as e:
    print(e)

try:
    print("\nstart4")
    subprocess.run("{} -f pdf {}".format(unoconv_path, docx_path),shell=True)
    print("\ndone4")
except Exception as e:
    print(e)