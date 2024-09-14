import subprocess
import shutil
import getpass

unoconv_path = shutil.which("unoconv")
# unoconv_path = "/usr/local/bin/unoconv -vvv "
docx_path="/data/www/ezwork_api/storage/app/public/translate/JRatLaQlYcWm4zAKC5NefpXh3cmG3y7cJ6EC58CU/aaa.docx"
pdf_path="/data/www/ezwork_api/storage/app/public/translate/JRatLaQlYcWm4zAKC5NefpXh3cmG3y7cJ6EC58CU/aaa.pdf"

username = getpass.getuser()
print(f"当前执行用户是: {username}\n")

# print("\n{} -f pdf -o {} {}\n".format(unoconv_path, pdf_path, docx_path))
# try:
#     process=subprocess.run("{} -vvv -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path), stdout=subprocess.PIPE, text=True)
#     print(process.stdout)
#     print("done\n")
# except Exception as e:
#     print(e)

# try:
#     print("\nstart2")
#     subprocess.run("{} -vvv -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path))
#     print("\ndone2")
# except Exception as e:
#     print(e)

try:
    print("\nstart3")
    subprocess.run("{} -vvv -f pdf -o {} {}".format(unoconv_path, pdf_path, docx_path),shell=True)
    print("\ndone3")
except Exception as e:
    print(e)

# try:
#     print("\nstart4")
#     subprocess.run("{} -vvv -f pdf {}".format(unoconv_path, docx_path),shell=True)
#     print("\ndone4")
# except Exception as e:
#     print(e)