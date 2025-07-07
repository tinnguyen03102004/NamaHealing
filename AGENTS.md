Các Agent trong Hệ Thống Quản Lý Lớp Học Zoom (NamaHealing)
Hệ thống quản lý lớp học Zoom của dự án NamaHealing được xây dựng xoay quanh một số agent (tác nhân) chính. Việc hiểu rõ vai trò và chức năng của từng agent giúp đảm bảo rằng việc phân quyền và luồng logic của hệ thống được triển khai chính xác. Dưới đây là mô tả chi tiết về các agent chính:
Học viên (Student)
Vai trò trong hệ thống: Học viên là người dùng cuối, tham gia các buổi học Zoom thông qua hệ thống web. Đây là đối tượng chính mà hệ thống phục vụ, với mục tiêu giúp họ dễ dàng đăng ký, đăng nhập và truy cập lớp học trực tuyến. Chức năng chính: Học viên có các chức năng sau trong hệ thống (qua giao diện người dùng web):
Đăng ký tài khoản: Tạo tài khoản mới bằng cách cung cấp thông tin cơ bản (họ tên, email, mật khẩu...). Mặc định khi tạo, tài khoản học viên có số buổi học ban đầu = 0 (chưa có buổi nào cho đến khi được admin cấp).
Đăng nhập: Đăng nhập vào hệ thống bằng email và mật khẩu đã đăng ký. Sau khi đăng nhập thành công, học viên được chuyển đến trang chính (dashboard) của mình.
Xem thông tin & số buổi còn lại: Trên trang dashboard, học viên thấy thông tin cá nhân quan trọng, đặc biệt là số buổi học còn lại trên tài khoản. Thông tin này được hiển thị nổi bật (ví dụ: “Bạn còn X buổi học”).
Truy cập link Zoom lớp học: Học viên thấy hai nút hoặc đường link rõ ràng để vào lớp buổi sáng và buổi chiều. Khi đến giờ học, học viên nhấn vào nút “Vào lớp sáng” hoặc “Vào lớp chiều” để tham gia buổi học tương ứng. Hệ thống sẽ xử lý yêu cầu này (thông qua tác nhân hệ thống, xem phần Session Manager & Zoom Redirect Handler) để quyết định cho phép vào lớp hay không.
Xem lịch sử tham gia: Học viên có thể xem trang “Lịch sử học tập” liệt kê các buổi học đã tham gia. Mỗi bản ghi lịch sử bao gồm thông tin như ngày giờ tham gia, loại lớp (sáng/chiều), và số buổi còn lại sau mỗi lần tham gia. Điều này giúp học viên tự theo dõi số buổi đã sử dụng và còn lại, biết khi nào cần mua thêm buổi.
Yêu cầu thêm buổi học: Khi sắp hết số buổi hoặc muốn học thêm, học viên sẽ liên hệ quản trị viên (ví dụ: qua email, tin nhắn hoặc chức năng gửi yêu cầu nếu có) để được hướng dẫn nạp thêm buổi. (Ghi chú: Việc thanh toán/nạp thêm buổi hiện tại được thực hiện ngoài hệ thống, ví dụ chuyển khoản ngân hàng và gửi minh chứng cho admin).
Đăng xuất: Sau khi sử dụng, học viên có thể đăng xuất khỏi hệ thống để đảm bảo an toàn cho tài khoản, nhất là khi dùng máy tính công cộng.
Tương tác với hệ thống & agent khác: Học viên chủ yếu tương tác với hệ thống qua giao diện web:
Học viên gửi thông tin đến Session Manager khi đăng ký, đăng nhập (Session Manager xử lý việc xác thực và tạo phiên đăng nhập cho học viên).
Khi học viên nhấn vào nút vào lớp, yêu cầu sẽ được chuyển đến Zoom Redirect Handler trên server. Zoom Redirect Handler kiểm tra trạng thái đăng nhập (qua Session Manager) và kiểm tra số buổi còn lại từ cơ sở dữ liệu trước khi cho phép học viên tham gia lớp.
Học viên gián tiếp tương tác với Admin khi cần nạp thêm buổi: admin sẽ cập nhật số buổi trong hệ thống để học viên thấy thay đổi trên tài khoản của mình.
Quản trị viên (Admin)
Vai trò trong hệ thống: Admin là người quản lý hệ thống, có quyền hạn cao hơn học viên. Quản trị viên chịu trách nhiệm quản lý tài khoản học viên, thiết lập thông tin lớp học và đảm bảo hệ thống vận hành đúng chính sách (như chỉ cho phép học viên còn buổi mới được vào lớp). Chức năng chính: Quản trị viên có các chức năng quản trị thông qua giao diện (hoặc API) chuyên biệt:
Đăng nhập admin: Admin đăng nhập qua trang đăng nhập (có thể cùng form với học viên nhưng hệ thống sẽ nhận biết và phân quyền admin sau khi đăng nhập). Tài khoản admin thường do hệ thống tạo sẵn hoặc do developer gán quyền trong cơ sở dữ liệu.
Xem danh sách học viên: Truy cập trang quản trị để xem toàn bộ danh sách học viên đã đăng ký. Danh sách hiển thị các thông tin như tên, email, số buổi còn lại, trạng thái tài khoản, v.v.
Xem chi tiết & lịch sử của học viên: Admin có thể chọn một học viên cụ thể để xem thông tin chi tiết, bao gồm lịch sử tham gia các buổi học của học viên đó (các lần vào lớp, thời gian, loại buổi học, số buổi đã trừ, còn lại...).
Thêm/cập nhật số buổi học cho học viên: Đây là chức năng quan trọng của admin. Khi học viên đã đóng tiền để mua thêm buổi học, admin sẽ tìm kiếm tài khoản học viên theo tên hoặc email, sau đó cập nhật (cộng thêm) số buổi học vào tài khoản của học viên. Ví dụ: nhập +10 buổi cho một học viên X và lưu lại – hệ thống sẽ tăng số buổi còn lại của học viên X thêm 10. (Hệ thống có thể cho phép admin nhập kèm ghi chú, ví dụ “Nạp 10 buổi ngày 24/06/2025” để làm bằng chứng quản lý). Ngay sau khi cập nhật, học viên tương ứng khi đăng nhập sẽ thấy số buổi của họ đã tăng. (Lưu ý: Đối với học viên mới đăng ký mà mặc định 0 buổi, admin cũng dùng chức năng này để cấp buổi ban đầu sau khi nhận học phí ban đầu.)
Cấu hình link Zoom: Admin quản lý hai đường link Zoom cố định cho lớp học (một cho lớp sáng, một cho lớp chiều). Có trang cấu hình để admin nhập hoặc thay đổi URL Zoom cho buổi sáng và buổi chiều. Thay đổi này cho phép hệ thống cập nhật link cho học viên sử dụng. (Ví dụ, nếu lớp học tạo phòng Zoom mới hoặc đổi mật khẩu phòng, admin sẽ cập nhật link/mật khẩu tại đây để học viên luôn truy cập link mới nhất từ hệ thống).
Xem thống kê & lịch sử hệ thống: Admin có thể xem báo cáo tổng quan về hoạt động lớp học, chẳng hạn như:
Lịch sử theo ngày: Danh sách học viên đã vào lớp sáng hoặc chiều trong ngày hôm nay (hoặc một ngày cụ thể).
Lịch sử theo học viên: Danh sách các lần tham gia của một học viên nhất định, để theo dõi mức độ chuyên cần và giải quyết thắc mắc (vd: nếu học viên cho rằng bị trừ nhầm buổi, admin kiểm tra lịch sử để xác minh).
Thống kê buổi học: Số buổi đã sử dụng trong một khoảng thời gian, tổng số buổi còn lại của toàn bộ học viên, v.v., giúp admin quản lý tổng quát.
Quản lý tài khoản học viên: (Chức năng nâng cao, có thể thêm sau) Admin có thể chỉnh sửa thông tin tài khoản của học viên (tên, email) hoặc khóa/mở khóa tài khoản nếu phát hiện lạm dụng. Ban đầu, hệ thống có thể chưa cần tính năng khóa tài khoản để giữ mọi thứ đơn giản.
Gửi thông báo chung: (Tính năng tùy chọn) Admin có thể soạn thông báo chung (ví dụ: thông báo nghỉ học, thay đổi lịch) để hiển thị trên trang của tất cả học viên.
Đăng xuất admin: Tương tự học viên, admin có thể đăng xuất khỏi hệ thống khi hoàn thành công việc nhằm bảo đảm phiên quản trị không bị sử dụng trái phép.
Tương tác với hệ thống & agent khác: Admin tương tác chủ yếu qua giao diện quản trị, và hệ thống (đặc biệt các tác nhân hệ thống) đảm bảo chỉ admin mới thực hiện được các chức năng nhạy cảm:
Khi admin đăng nhập, Session Manager xác thực và tạo phiên đăng nhập có gắn nhãn quyền admin. Các yêu cầu truy cập trang quản trị hoặc API quản trị đều được Session Manager/ hệ thống kiểm tra vai trò; nếu người dùng không phải admin, hệ thống sẽ chặn (trả lỗi hoặc chuyển hướng khỏi trang admin).
Khi admin cập nhật số buổi hoặc xem danh sách học viên, các thao tác này đi qua Business Logic Layer trên server để truy cập cơ sở dữ liệu (bảng users và bảng lịch sử sessions). Session Manager đảm bảo rằng chỉ yêu cầu của admin hợp lệ mới có thể cập nhật dữ liệu này.
Admin cấu hình link Zoom: khi admin lưu link mới, hệ thống (có thể qua một Config Manager hoặc trực tiếp DB) cập nhật bảng zoom_links. Sau đó, khi học viên nhấn "vào lớp", Zoom Redirect Handler sẽ sử dụng thông tin link Zoom do admin cấu hình mới nhất.
Tóm lại, admin tương tác chặt chẽ với các tác nhân hệ thống: mọi hành động của admin đều qua Session Manager (để xác thực và phân quyền) và tác động đến dữ liệu mà Zoom Redirect Handler và các thành phần khác sử dụng.
Các tác nhân hệ thống (System Agents)
Bên cạnh hai loại người dùng chính (học viên và admin), hệ thống còn có các agent hệ thống – đây là các thành phần/chức năng tự động chạy trên server, đảm nhiệm logic nghiệp vụ và đảm bảo hệ thống vận hành đúng quy tắc. Hai tác nhân hệ thống quan trọng trong dự án này là Session Manager và Zoom Redirect Handler:
Session Manager (Quản lý phiên đăng nhập)
Vai trò: Session Manager chịu trách nhiệm quản lý phiên làm việc (session) cho cả học viên và admin. Nó đảm bảo việc xác thực người dùng khi đăng nhập, lưu trạng thái đăng nhập, và cung cấp thông tin phiên (như user ID, vai trò) cho các phần khác của hệ thống. Session Manager là nền tảng của cơ chế phân quyền và bảo mật phiên trong hệ thống. Chức năng chính:
Xác thực đăng nhập: Khi người dùng (học viên hoặc admin) gửi thông tin đăng nhập, Session Manager kiểm tra thông tin đó với cơ sở dữ liệu (users). Nếu hợp lệ, Session Manager tạo một phiên đăng nhập mới.
Tạo và duy trì session: Sau khi đăng nhập thành công, Session Manager tạo một session ID duy nhất cho người dùng và thường gửi về trình duyệt dưới dạng cookie (có cờ bảo mật như HTTPOnly). Thông qua session này, hệ thống nhận biết người dùng trong các lần truy cập tiếp theo mà không yêu cầu đăng nhập lại mỗi lần.
Lưu trữ thông tin vai trò: Session Manager gắn thông tin về vai trò (học viên hay admin) vào phiên đăng nhập. Nhờ đó, khi người dùng truy cập các trang khác nhau, hệ thống có thể kiểm tra session để biết quyền của user hiện tại.
Xác thực cho mỗi yêu cầu: Mọi request từ client lên server đi kèm token/cookie phiên. Session Manager (hoặc middleware liên quan) sẽ kiểm tra token này để xác định người dùng đã đăng nhập hay chưa và có quyền truy cập hay không. Nếu không có phiên hợp lệ, yêu cầu sẽ bị từ chối hoặc người dùng bị chuyển hướng tới trang đăng nhập.
Phân quyền truy cập: Dựa trên thông tin phiên, Session Manager đảm bảo:
Học viên không thể truy cập các trang/chức năng dành riêng cho admin. Nếu một user không phải admin mà cố gọi API quản trị hoặc truy cập trang admin, hệ thống sẽ từ chối (trả về lỗi 403 hoặc redirect ra trang khác).
Tương tự, Session Manager buộc phải đăng nhập mới được vào các trang yêu cầu đăng nhập (như trang “vào lớp”). Nếu chưa đăng nhập, khi truy cập những trang này sẽ bị chuyển về giao diện đăng nhập.
Đăng xuất & hủy phiên: Khi người dùng chọn đăng xuất, Session Manager xóa hoặc vô hiệu hóa session tương ứng (xóa cookie phía client và xóa thông tin phiên phía server), bảo đảm thông tin đăng nhập không còn hiệu lực.
Tương tác với hệ thống & agent khác:
Session Manager tương tác trực tiếp với cơ sở dữ liệu người dùng: kiểm tra thông tin đăng nhập, lấy dữ liệu vai trò, và (nếu sử dụng server-side session lưu trong DB) thì tạo bản ghi session mới.
Tất cả các phần của hệ thống sử dụng thông tin người dùng đang đăng nhập (ví dụ: hiển thị tên học viên trên giao diện, kiểm tra quyền admin, lấy số buổi còn lại để hiển thị) đều thông qua Session Manager để biết ai đang đăng nhập và lấy dữ liệu liên quan.
Zoom Redirect Handler (và các route API khác) phụ thuộc vào Session Manager để xác thực người dùng. Trước khi cho phép vào lớp hoặc xử lý yêu cầu nhạy cảm, chúng gọi Session Manager (hoặc sử dụng middleware do Session Manager cung cấp) để đảm bảo request có session hợp lệ và đúng quyền.
Session Manager nâng cao tính bảo mật: Nó thiết lập các thuộc tính bảo mật cho session (như HTTPOnly cookie, expiry time, v.v.) và có thể tích hợp các biện pháp như giới hạn số lần đăng nhập sai, captcha... nhằm bảo vệ hệ thống khỏi truy cập trái phép.
Zoom Redirect Handler (Bộ xử lý chuyển hướng Zoom)
Vai trò: Zoom Redirect Handler chịu trách nhiệm xử lý yêu cầu vào lớp học Zoom của người dùng. Đây là thành phần quan trọng đảm bảo rằng chỉ những học viên hợp lệ (đăng nhập rồi và còn buổi học) mới được phép truy cập phòng Zoom thông qua hệ thống. Zoom Redirect Handler thực thi quy tắc nghiệp vụ cốt lõi: trừ số buổi học khi tham gia lớp và chuyển hướng đến phòng Zoom. Chức năng chính:
Tiếp nhận yêu cầu vào lớp: Khi học viên nhấn “Vào lớp sáng” hoặc “Vào lớp chiều”, một request được gửi đến server (ví dụ: đến endpoint nội bộ /join-class?session=morning hoặc /join/morning). Zoom Redirect Handler lắng nghe các yêu cầu này.
Kiểm tra xác thực: Ngay khi nhận yêu cầu, Zoom Redirect Handler kiểm tra với Session Manager xem người dùng đã đăng nhập hay chưa:
Nếu chưa đăng nhập, nó sẽ chặn lại và thường yêu cầu người dùng đăng nhập (có thể redirect về trang login).
Nếu đã đăng nhập, tiếp tục kiểm tra bước tiếp theo.
Kiểm tra số buổi còn lại: Zoom Redirect Handler tra cứu cơ sở dữ liệu (thông qua lớp logic nghiệp vụ hoặc trực tiếp) để lấy số buổi học còn lại của học viên yêu cầu:
Nếu số buổi > 0 (còn buổi): Học viên đủ điều kiện tham gia. Handler sẽ tiến hành cho phép tham gia lớp.
Nếu số buổi = 0: Học viên đã hết buổi. Handler chặn truy cập, không cấp link Zoom. Thay vào đó, trả về thông báo lỗi hoặc trang cảnh báo (ví dụ: “Bạn đã hết số buổi học, liên hệ quản trị viên để nạp thêm.”). Học viên sẽ không vào được phòng Zoom qua hệ thống trong trường hợp này.
Cập nhật số buổi & ghi lịch sử: Trường hợp học viên đủ điều kiện (còn buổi):
Zoom Redirect Handler thực hiện trừ 1 buổi từ tài khoản học viên trong cơ sở dữ liệu (giảm số buổi còn lại đi 1 trước khi cho vào lớp).
Đồng thời, tạo một bản ghi lịch sử (trong bảng sessions hoặc bảng lịch sử) lưu thông tin: mã học viên, thời gian tham gia, loại lớp (sáng/chiều), số buổi còn lại sau khi trừ. Bước này đảm bảo mọi lượt tham gia đều được log để sau này admin và học viên có thể đối chiếu.
Chuyển hướng đến Zoom: Sau khi cập nhật dữ liệu thành công, Zoom Redirect Handler thực hiện việc chuyển hướng người dùng sang phòng Zoom thật. Có hai cách phổ biến:
Cách 1: Trả về một trang web có nút “Nhấn vào đây để vào Zoom” cùng link Zoom thật (được nhúng trong nút). Cách này cho người dùng thêm một bước chủ động nhấn nút, tránh việc bị bất ngờ chuyển ứng dụng.
Cách 2: Gửi phản hồi HTTP redirect 302 trực tiếp đến URL Zoom. Cách này tự động mở phòng Zoom cho học viên. (Lưu ý: Dự án ưu tiên cách 1 để user chủ động, tuy nhiên cả hai cách đều có thể tùy chỉnh theo nhu cầu).
Bảo vệ link Zoom: Quan trọng, Zoom Redirect Handler đảm bảo học viên luôn phải đi qua hệ thống để vào lớp, thay vì truy cập thẳng link Zoom. Link Zoom thật (URL phòng Zoom, bao gồm cả mật khẩu nếu có) thường không lộ trực tiếp trên giao diện nếu học viên chưa đăng nhập hoặc hết buổi. Thay vào đó, link này được “gói” thành URL nội bộ (như .../join/morning). Điều này ngăn người không có tài khoản (hoặc học viên đã hết buổi) sử dụng link một cách tùy tiện.
Tương tác với hệ thống & agent khác:
Session Manager: Đây là bước đầu tiên Zoom Redirect Handler tương tác – kiểm tra session đăng nhập và vai trò. Nếu người dùng chưa được Session Manager xác thực, Zoom Redirect Handler sẽ dừng tiến trình và yêu cầu đăng nhập.
Cơ sở dữ liệu (bảng users, sessions, zoom_links): Zoom Redirect Handler đọc và ghi dữ liệu:
Đọc số buổi còn lại của học viên từ bảng users (hoặc bảng liên quan).
Ghi lại số buổi mới (sau khi trừ) vào bảng users.
Tạo bản ghi mới trong bảng sessions (lịch sử tham gia).
Đọc URL Zoom từ bảng zoom_links (lấy link tương ứng cho sáng hoặc chiều do admin đã cấu hình).
Admin (gián tiếp): Zoom Redirect Handler sử dụng dữ liệu do admin cung cấp:
Link Zoom được admin cấu hình sẽ được Handler dùng để redirect học viên.
Nếu admin tạm khóa một học viên hoặc đặt hạn chế, Handler cần phối hợp với Session Manager để chặn học viên đó (nếu tài khoản bị khóa, có thể coi như không hợp lệ để tham gia).
Giao diện người dùng: Tùy thuộc vào kết quả kiểm tra, Zoom Redirect Handler gửi phản hồi cho client:
Nếu thành công (còn buổi): có thể hiển thị trang trung gian với nút vào Zoom, kèm thông tin cập nhật (ví dụ: “Bạn còn X-1 buổi”).
Nếu thất bại: hiển thị thông báo lỗi, đảm bảo người dùng hiểu lý do không vào được lớp (chưa đăng nhập hoặc hết buổi).