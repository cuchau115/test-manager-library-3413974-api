<?php

namespace App\Infrastructure\Persistence\Repositories;

use App\Application\Ports\MemberRepository;
use App\Domain\Model\Member;
use App\Infrastructure\Persistence\Models\MemberModel;

final class EloquentMemberRepository implements MemberRepository
{
    public function nextIdentity(): string
    {
        return (string) \Ramsey\Uuid\Uuid::uuid4();
    }

    public function save(Member $member): void
    {
        $model = MemberModel::find($member->id());
        if ($model === null) {
            $model = new MemberModel();
            $model->id = $member->id();
        }

        $model->full_name = $member->fullName();
        $model->document_number = $member->documentNumber();
        $model->save();
    }

    public function findById(string $id): ?Member
    {
        $model = MemberModel::find($id);
        return $model ? $this->toDomain($model) : null;
    }

    public function findByDocument(string $documentNumber): ?Member
    {
        $model = MemberModel::where('document_number', $documentNumber)->first();
        return $model ? $this->toDomain($model) : null;
    }

    /**
     * @return array{items: list<Member>, total: int}
     */
    public function search(?string $text, int $page, int $size): array
    {
        $query = MemberModel::orderBy('full_name');

        if ($text !== null && $text !== '') {
            $query->where(function ($q) use ($text) {
                $q->where('full_name', 'like', "%{$text}%")
                  ->orWhere('document_number', 'like', "%{$text}%");
            });
        }

        $total = $query->count();
        $models = $query->skip(($page - 1) * $size)->take($size)->get();

        return [
            'items' => $models->map(fn ($m) => $this->toDomain($m))->all(),
            'total' => $total,
        ];
    }

    private function toDomain(MemberModel $model): Member
    {
        return Member::reconstitute(
            $model->id,
            $model->full_name,
            $model->document_number,
        );
    }
}