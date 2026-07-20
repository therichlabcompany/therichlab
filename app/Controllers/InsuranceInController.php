<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;
use Config\Database;

class InsuranceInController extends BaseController
{
    private $db;

    public function __construct()
    {
        helper(['form', 'url']);
        $this->db = Database::connect('default');
    }

    public function index(): string
    {
        $page = max(1, (int) $this->request->getGet('page'));
        $perPage = 10;
        $keyword = trim((string) $this->request->getGet('q'));
        $sort = (string) $this->request->getGet('sort');
        $sort = in_array($sort, ['answers', 'questions', 'views'], true) ? $sort : 'answers';

        $builder = $this->listBuilder($keyword);
        $total = $builder->countAllResults(false);
        if ($sort === 'views') {
            $builder->orderBy('q.view_count', 'DESC')->orderBy('q.question_id', 'DESC');
        } elseif ($sort === 'questions') {
            $builder->orderBy('q.created_at', 'DESC');
        } else {
            $builder->orderBy('last_answer_at IS NULL', 'ASC', false)
                ->orderBy('last_answer_at', 'DESC')->orderBy('q.created_at', 'DESC');
        }

        return $this->renderView('insurance_in/index', [
            'header_class' => 'insurance-in-page',
            'questions' => $builder->limit($perPage, ($page - 1) * $perPage)->get()->getResultArray(),
            'keyword' => $keyword,
            'sort' => $sort,
            'page' => $page,
            'total_pages' => max(1, (int) ceil($total / $perPage)),
        ]);
    }

    public function view(int $id): string
    {
        $question = $this->question($id);
        $this->db->table('my_fc_insurance_in_question')->where('question_id', $id)
            ->set('view_count', 'view_count + 1', false)->update();
        $question['view_count'] = (int) $question['view_count'] + 1;

        $answers = $this->db->table('my_fc_insurance_in_answer a')
            ->select('a.*, m.name, m.profile_image, p.company, p.company_sub, p.ga, p.license_date, pa.region, pa.insurance_types,
                IFNULL(rv.rating, 0) rating, IFNULL(rv.rating_count, 0) rating_count', false)
            ->join('my_fc_member m', 'm.member_uid = a.fc_member_uid', 'inner')
            ->join('my_fc_profile p', 'p.member_uid = a.fc_member_uid', 'left')
            ->join('my_fc_profile_activity pa', 'pa.member_uid = a.fc_member_uid', 'left')
            ->join('(SELECT fc_member_uid, AVG(rating) rating, COUNT(*) rating_count FROM my_fc_counsel_review WHERE deleted_at IS NULL GROUP BY fc_member_uid) rv', 'rv.fc_member_uid = a.fc_member_uid', 'left', false)
            ->where('a.question_id', $id)->where('a.status', 'DISPLAY')->where('a.deleted_at', null)
            ->orderBy('a.created_at', 'ASC')->get()->getResultArray();

        return $this->renderView('insurance_in/view', [
            'header_class' => 'insurance-in-page insurance-in-detail',
            'question' => $question,
            'answers' => $answers,
            'files' => $this->db->table('my_fc_insurance_in_file')->where('question_id', $id)->get()->getResultArray(),
        ]);
    }

    public function write(?int $id = null): string
    {
        $this->requireMember('USER');
        $question = $id ? $this->question($id) : null;
        if ($question) {
            $this->requireOwner($question['member_uid']);
            if ((int) $question['answer_count'] > 0) {
                return redirect()->to('/insurance-in/' . $id)->with('error', '답변이 등록된 질문은 수정할 수 없습니다.');
            }
        }
        return $this->renderView('insurance_in/write', [
            'header_class' => 'form-page insurance-in-page insurance-in-form-page', 'question' => $question,
        ]);
    }

    public function saveQuestion(?int $id = null)
    {
        $this->requireMember('USER');
        $title = trim((string) $this->request->getPost('title'));
        $body = trim((string) $this->request->getPost('body'));
        if (mb_strlen($title) < 10 || mb_strlen($title) > 50 || mb_strlen($body) < 10 || !$this->request->getPost('agree_notice')) {
            return redirect()->back()->withInput()->with('error', '제목은 10자 이상 50자 이하, 내용은 10자 이상 입력하고 주의사항에 동의해주세요.');
        }

        $forbiddenWord = $this->forbiddenWordViolation($title . "\n" . $body, ['ALL']);
        if ($forbiddenWord !== null) {
            return redirect()->back()->withInput()->with('error', $this->forbiddenWordErrorMessage('보험IN 글', $forbiddenWord));
        }

        if ($id) {
            $question = $this->question($id);
            $this->requireOwner($question['member_uid']);
            if ((int) $question['answer_count'] > 0) {
                return redirect()->to('/insurance-in/' . $id)->with('error', '답변이 등록된 질문은 수정할 수 없습니다.');
            }
            $this->db->table('my_fc_insurance_in_question')->where('question_id', $id)->update(['title' => $title, 'body' => $body]);
        } else {
            $this->db->table('my_fc_insurance_in_question')->insert([
                'member_uid' => session()->get('member_uid'), 'title' => $title, 'body' => $body,
            ]);
            $id = (int) $this->db->insertID();
        }
        $uploadError = $this->saveUpload($id);
        return redirect()->to('/insurance-in/' . $id)->with($uploadError ? 'error' : 'message', $uploadError ?: '질문이 등록되었습니다.');
    }

    public function deleteQuestion(int $id)
    {
        $this->requireMember('USER');
        $question = $this->question($id);
        $this->requireOwner($question['member_uid']);
        if ((int) $question['answer_count'] > 0) {
            return redirect()->back()->with('error', '답변이 등록된 질문은 삭제할 수 없습니다.');
        }
        $this->db->table('my_fc_insurance_in_question')->where('question_id', $id)
            ->update(['status' => 'DELETED', 'deleted_at' => date('Y-m-d H:i:s')]);
        return redirect()->to('/insurance-in')->with('message', '질문이 삭제되었습니다.');
    }

    public function answer(int $questionId, ?int $answerId = null): string
    {
        $this->requireMember('FC');
        $answer = null;
        if ($answerId) {
            $answer = $this->answerRow($questionId, $answerId);
            $this->requireOwner($answer['fc_member_uid']);
        }
        return $this->renderView('insurance_in/answer', [
            'header_class' => 'insurance-in-page insurance-in-answer-page',
            'question' => $this->question($questionId), 'answer' => $answer,
            'fc' => $this->fc(session()->get('member_uid')),
        ]);
    }

    public function saveAnswer(int $questionId, ?int $answerId = null)
    {
        $this->requireMember('FC');
        $body = trim((string) $this->request->getPost('answer'));
        if ($body === '' || !$this->request->getPost('agree_notice')) {
            return redirect()->back()->withInput()->with('error', '답변 내용을 입력하고 주의사항에 동의해주세요.');
        }

        $forbiddenWord = $this->forbiddenWordViolation($body, ['COUNSEL']);
        if ($forbiddenWord !== null) {
            return redirect()->back()->withInput()->with('error', $this->forbiddenWordErrorMessage('보험IN 답변', $forbiddenWord));
        }

        $this->question($questionId);
        $uid = session()->get('member_uid');
        if ($answerId) {
            $answer = $this->answerRow($questionId, $answerId);
            $this->requireOwner($answer['fc_member_uid']);
            $this->db->table('my_fc_insurance_in_answer')->where('answer_id', $answerId)->update(['body' => $body]);
        } else {
            $previous = $this->db->table('my_fc_insurance_in_answer')
                ->where(['question_id' => $questionId, 'fc_member_uid' => $uid])->get()->getRowArray();
            if ($previous && $previous['status'] === 'DISPLAY') {
                return redirect()->to('/insurance-in/' . $questionId)->with('error', '이미 이 질문에 답변을 등록했습니다.');
            }
            if ($previous) {
                $this->db->table('my_fc_insurance_in_answer')->where('answer_id', $previous['answer_id'])->update([
                    'body' => $body, 'status' => 'DISPLAY', 'deleted_at' => null, 'created_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->db->table('my_fc_insurance_in_answer')->insert(['question_id' => $questionId, 'fc_member_uid' => $uid, 'body' => $body]);
            }
        }
        return redirect()->to('/insurance-in/' . $questionId)->with('message', '답변이 저장되었습니다.');
    }

    public function deleteAnswer(int $questionId, int $answerId)
    {
        $this->requireMember('FC');
        $answer = $this->answerRow($questionId, $answerId);
        $this->requireOwner($answer['fc_member_uid']);
        $this->db->table('my_fc_insurance_in_answer')->where('answer_id', $answerId)
            ->update(['status' => 'DELETED', 'deleted_at' => date('Y-m-d H:i:s')]);
        return redirect()->to('/insurance-in/' . $questionId)->with('message', '답변이 삭제되었습니다.');
    }

    public function download(int $fileId)
    {
        $row = $this->db->table('my_fc_insurance_in_file')->where('file_id', $fileId)->get()->getRowArray();
        if (!$row) {
            throw PageNotFoundException::forPageNotFound();
        }

        // 기존 저장값(writable/uploads/...)과 파일명만 저장된 데이터 모두 지원한다.
        $storedPath = ltrim(str_replace('\\', '/', (string) ($row['file_path'] ?? '')), '/');
        $fileName = basename((string) ($row['saved_name'] ?? $storedPath));
        $candidates = [
            ROOTPATH . $storedPath,
            WRITEPATH . 'uploads/insurance_in/' . $fileName,
        ];

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if ($filePath === null) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->download($filePath, null)->setFileName($row['original_name']);
    }

    private function listBuilder(string $keyword = '')
    {
        $builder = $this->db->table('my_fc_insurance_in_question q')
            ->select('q.*, COUNT(a.answer_id) answer_count, MAX(a.created_at) last_answer_at,
                SUBSTRING_INDEX(GROUP_CONCAT(m.name ORDER BY a.created_at ASC), ",", 1) first_fc_name', false)
            ->join('my_fc_insurance_in_answer a', "a.question_id = q.question_id AND a.status = 'DISPLAY' AND a.deleted_at IS NULL", 'left', false)
            ->join('my_fc_member m', 'm.member_uid = a.fc_member_uid', 'left')
            ->where('q.status', 'OPEN')->where('q.deleted_at', null)->groupBy('q.question_id');
        if ($keyword !== '') $builder->groupStart()->like('q.title', $keyword)->orLike('q.body', $keyword)->groupEnd();
        return $builder;
    }

    private function question(int $id): array
    {
        $row = $this->listBuilder()->where('q.question_id', $id)->get()->getRowArray();
        if (!$row) throw PageNotFoundException::forPageNotFound();
        return $row;
    }

    private function answerRow(int $questionId, int $answerId): array
    {
        $row = $this->db->table('my_fc_insurance_in_answer')->where(['question_id' => $questionId, 'answer_id' => $answerId, 'status' => 'DISPLAY'])->get()->getRowArray();
        if (!$row) throw PageNotFoundException::forPageNotFound();
        return $row;
    }

    private function fc(string $uid): array
    {
        return $this->db->table('my_fc_member m')->select('m.name, m.profile_image, p.company, pa.region, pa.insurance_types')
            ->join('my_fc_profile p', 'p.member_uid=m.member_uid', 'left')->join('my_fc_profile_activity pa', 'pa.member_uid=m.member_uid', 'left')
            ->where('m.member_uid', $uid)->get()->getRowArray() ?? [];
    }

    private function requireMember(?string $type = null): void
    {
        if (!session()->get('logged_in') || !session()->get('member_uid')) {
            redirect()->to('/member/login')->with('error', '로그인이 필요합니다.')->send(); exit;
        }
        if ($type && session()->get('member_type') !== $type) {
            redirect()->to('/insurance-in')->with('error', $type === 'FC' ? 'FC 회원만 답변할 수 있습니다.' : '개인회원만 글작성이 가능합니다.')->send(); exit;
        }
    }

    private function requireOwner(string $uid): void
    {
        if ((string) session()->get('member_uid') !== $uid) {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    private function saveUpload(int $questionId): ?string
    {
        $file = $this->request->getFile('attach_file');
        if (!$file || $file->getError() === UPLOAD_ERR_NO_FILE) return null;
        $allowed = ['pdf', 'xls', 'xlsx', 'hwp', 'jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower($file->getExtension());
        if (!$file->isValid() || !in_array($ext, $allowed, true) || $file->getSize() > 20 * 1024 * 1024) {
            return '첨부파일은 20MB 이하의 pdf, xls, xlsx, hwp, jpg, png, gif만 가능합니다.';
        }
        $dir = WRITEPATH . 'uploads/insurance_in';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        $saved = $file->getRandomName();
        $file->move($dir, $saved);
        $this->db->table('my_fc_insurance_in_file')->insert([
            'question_id' => $questionId, 'original_name' => $file->getClientName(), 'saved_name' => $saved,
            'file_path' => 'writable/uploads/insurance_in/' . $saved, 'file_ext' => $ext, 'file_size' => $file->getSize(),
        ]);
        return null;
    }
}
